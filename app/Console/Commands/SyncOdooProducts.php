<?php

namespace App\Console\Commands;

use App\Models\OdooConnection;
use App\Services\Odoo\OdooException;
use App\Services\Odoo\OdooProductSyncService;
use Illuminate\Console\Command;

class SyncOdooProducts extends Command
{
    protected $signature = 'odoo:sync {--store= : Sincronizar solo el comercio con este ID}';

    protected $description = 'Sincroniza los productos de Odoo de los comercios con la integración activa';

    public function handle(OdooProductSyncService $service): int
    {
        $connections = OdooConnection::query()
            ->where('enabled', true)
            ->when($this->option('store'), fn ($query, $storeId) => $query->where('store_id', $storeId))
            ->with('store.subscription.plan')
            ->get();

        foreach ($connections as $connection) {
            $subscription = $connection->store?->subscription;

            if (! $subscription || ! $subscription->hasFeature('has_api')) {
                $this->line("Comercio {$connection->store_id}: el plan no incluye la integración, se omite.");

                continue;
            }

            try {
                $stats = $service->sync($connection);
            } catch (OdooException $e) {
                $this->error("Comercio {$connection->store_id}: {$e->getMessage()}");

                continue;
            }

            if ($stats === null) {
                $this->line("Comercio {$connection->store_id}: ya hay una sincronización en curso.");

                continue;
            }

            $this->info("Comercio {$connection->store_id}: {$stats['created']} nuevos, {$stats['updated']} actualizados, {$stats['deactivated']} desactivados.");
        }

        return self::SUCCESS;
    }
}
