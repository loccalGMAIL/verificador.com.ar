<?php

namespace App\Services\Odoo;

use App\Models\OdooConnection;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Sincroniza el catálogo de Odoo (product.product) hacia los productos del comercio.
 *
 * - Incremental: solo trae registros con write_date >= sync_cursor.
 * - Upsert por odoo_product_id y, si no existe, por código de barras.
 * - Los productos archivados en Odoo se desactivan acá.
 * - Respeta el límite de productos del plan para altas nuevas.
 */
class OdooProductSyncService
{
    private const FIELDS = ['id', 'barcode', 'name', 'list_price', 'active', 'write_date'];

    /**
     * Devuelve las estadísticas, o null si ya hay otra sincronización en curso para el comercio.
     *
     * @return array{created: int, updated: int, deactivated: int, unchanged: int, skipped_limit: int, skipped_conflict: int}|null
     *
     * @throws OdooException
     */
    public function sync(OdooConnection $connection): ?array
    {
        $lock = Cache::lock("odoo-sync:{$connection->id}", 600);

        if (! $lock->get()) {
            return null;
        }

        try {
            return $this->runSync($connection);
        } catch (OdooException $e) {
            $connection->update([
                'last_synced_at' => now(),
                'last_sync_status' => 'failed',
                'last_error' => $e->getMessage(),
            ]);

            Log::warning('Odoo sync failed', ['store_id' => $connection->store_id, 'error' => $e->getMessage()]);

            throw $e;
        } finally {
            $lock->release();
        }
    }

    /**
     * @return array{created: int, updated: int, deactivated: int, unchanged: int, skipped_limit: int, skipped_conflict: int}
     */
    private function runSync(OdooConnection $connection): array
    {
        $client = OdooClient::forConnection($connection);
        $store = $connection->store;
        $pageSize = max(1, (int) config('services.odoo.page_size', 200));

        $domain = [['barcode', '!=', false]];
        if ($connection->sync_cursor) {
            $domain[] = ['write_date', '>=', $connection->sync_cursor];
        }

        $stats = ['created' => 0, 'updated' => 0, 'deactivated' => 0, 'unchanged' => 0, 'skipped_limit' => 0, 'skipped_conflict' => 0];
        $remainingSlots = $this->remainingProductSlots($store);
        $cursor = $connection->sync_cursor;
        $offset = 0;

        do {
            $records = $client->searchRead(
                'product.product',
                $domain,
                self::FIELDS,
                $pageSize,
                $offset,
                'write_date asc, id asc',
                ['active_test' => false],
            );

            foreach ($records as $record) {
                $outcome = $this->upsertProduct($store, $record, $remainingSlots);
                $stats[$outcome]++;

                if ($outcome === 'created' && $remainingSlots !== null) {
                    $remainingSlots--;
                }

                $writeDate = is_string($record['write_date'] ?? null) ? $record['write_date'] : null;
                if ($writeDate !== null && ($cursor === null || strcmp($writeDate, $cursor) > 0)) {
                    $cursor = $writeDate;
                }
            }

            $offset += $pageSize;
        } while (count($records) === $pageSize);

        $connection->update([
            'sync_cursor' => $cursor,
            'last_synced_at' => now(),
            'last_sync_status' => 'success',
            'last_sync_stats' => $stats,
            'last_error' => null,
        ]);

        return $stats;
    }

    /**
     * @param  array<string, mixed>  $record
     * @return 'created'|'updated'|'deactivated'|'unchanged'|'skipped_limit'|'skipped_conflict'
     */
    private function upsertProduct(Store $store, array $record, ?int $remainingSlots): string
    {
        $odooId = (int) $record['id'];
        $barcode = trim((string) ($record['barcode'] ?: ''));
        $name = trim((string) ($record['name'] ?: ''));
        $active = (bool) ($record['active'] ?? true);
        $price = (float) ($record['list_price'] ?? 0);

        if ($barcode === '' || $name === '') {
            return 'unchanged';
        }

        $product = Product::where('store_id', $store->id)->where('odoo_product_id', $odooId)->first()
            ?? Product::where('store_id', $store->id)->where('barcode', $barcode)->first();

        if (! $product) {
            if (! $active) {
                return 'unchanged';
            }

            if ($remainingSlots !== null && $remainingSlots <= 0) {
                return 'skipped_limit';
            }

            Product::create([
                'store_id' => $store->id,
                'odoo_product_id' => $odooId,
                'source' => 'odoo',
                'barcode' => $barcode,
                'name' => $name,
                'price' => $price > 0 ? $price : null,
                'active' => true,
            ]);

            return 'created';
        }

        // El código cambió en Odoo y ya lo usa otro producto del comercio
        if ($product->barcode !== $barcode) {
            $taken = Product::where('store_id', $store->id)
                ->where('barcode', $barcode)
                ->where('id', '!=', $product->id)
                ->exists();

            if ($taken) {
                return 'skipped_conflict';
            }
        }

        $wasActive = (bool) $product->active;

        $product->fill([
            'odoo_product_id' => $odooId,
            'source' => 'odoo',
            'barcode' => $barcode,
            'name' => $name,
            'price' => $price > 0 ? $price : null,
            'active' => $active,
        ]);

        if (! $product->isDirty()) {
            return 'unchanged';
        }

        $product->save();

        return $wasActive && ! $active ? 'deactivated' : 'updated';
    }

    /** Lugares disponibles para productos nuevos según el plan (null = sin límite) */
    private function remainingProductSlots(Store $store): ?int
    {
        $subscription = $store->subscription;

        if (! $subscription || $subscription->hasFullAccess() || $subscription->plan?->max_products === null) {
            return null;
        }

        $activeCount = $store->products()->where('active', true)->count();

        return max(0, $subscription->plan->max_products - $activeCount);
    }
}
