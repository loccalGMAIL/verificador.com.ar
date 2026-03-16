<?php

namespace App\Jobs;

use App\Models\PriceList;
use App\Models\Product;
use App\Models\ProductImport;
use App\Models\ProductPrice;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Throwable;

class ProcessProductImport implements ShouldQueue
{
    use Queueable;

    public int $tries   = 1;
    public int $timeout = 300;

    public function __construct(
        public readonly ProductImport $import
    ) {}

    public function handle(): void
    {
        $this->import->update(['status' => 'processing']);

        try {
            $absolutePath = Storage::disk('local')->path($this->import->file_name);
            $spreadsheet  = IOFactory::load($absolutePath);
            $sheet        = $spreadsheet->getActiveSheet();
            $rows         = $sheet->toArray(null, true, true, false);

            // Quitar la primera fila (encabezados)
            array_shift($rows);

            // Obtener el mapeo guardado o intentar detección automática
            $col = $this->resolveMapping($this->import->mapping, $rows);

            if ($col === null) {
                $this->import->update([
                    'status'    => 'failed',
                    'error_log' => [['row' => 0, 'error' => 'No se pudo determinar el mapeo de columnas.']],
                ]);
                return;
            }

            // Resolver lista de precios destino
            $priceList = $this->import->price_list_id
                ? PriceList::find($this->import->price_list_id)
                : PriceList::where('store_id', $this->import->store_id)
                           ->where('is_default', true)
                           ->first();

            $rowsTotal = count($rows);
            $rowsOk    = 0;
            $rowsError = 0;
            $errorLog  = [];

            foreach ($rows as $index => $row) {
                $rowNum = $index + 2;

                // Saltar filas vacías
                $filled = array_filter($row, fn ($v) => $v !== null && $v !== '');
                if (empty($filled)) {
                    $rowsTotal--;
                    continue;
                }

                $barcode  = $col['barcode'] !== null ? trim((string) ($row[$col['barcode']] ?? '')) : '';
                $name     = $col['name']    !== null ? trim((string) ($row[$col['name']]    ?? '')) : '';
                $priceArs = $col['price_ars'] !== null ? $this->parseDecimal($row[$col['price_ars']] ?? null) : null;
                $priceUsd = $col['price_usd'] !== null ? $this->parseDecimal($row[$col['price_usd']] ?? null) : null;
                $currency = strtoupper(trim((string) ($col['currency'] !== null
                    ? ($row[$col['currency']] ?? 'ARS')
                    : 'ARS')));
                $desc     = $col['desc'] !== null ? trim((string) ($row[$col['desc']] ?? '')) : null;

                // Validaciones
                if ($barcode === '') {
                    $errorLog[] = ['row' => $rowNum, 'error' => 'Código de barras vacío'];
                    $rowsError++;
                    continue;
                }
                if ($name === '') {
                    $errorLog[] = ['row' => $rowNum, 'error' => "Nombre vacío (barcode: {$barcode})"];
                    $rowsError++;
                    continue;
                }
                if ($priceArs === null && $priceUsd === null) {
                    // Sin precio: se crea el producto pero sin precio en la lista
                }
                if (! in_array($currency, ['ARS', 'USD'])) {
                    $currency = 'ARS';
                }

                // Upsert del producto (campos base)
                $product = Product::updateOrCreate(
                    ['store_id' => $this->import->store_id, 'barcode' => $barcode],
                    [
                        'name'             => $name,
                        'description'      => $desc ?: null,
                        'price_ars'        => $priceArs,   // legacy sync
                        'price_usd'        => $priceUsd,   // legacy sync
                        'currency_default' => $currency,
                        'active'           => true,
                    ]
                );

                // Guardar precio en la lista destino
                if ($priceList && ($priceArs !== null || $priceUsd !== null)) {
                    ProductPrice::updateOrCreate(
                        ['product_id' => $product->id, 'price_list_id' => $priceList->id],
                        [
                            'price_ars'        => $priceArs,
                            'price_usd'        => $priceUsd,
                            'currency_default' => $currency,
                        ]
                    );
                }

                $rowsOk++;
            }

            $this->import->update([
                'status'     => 'completed',
                'rows_total' => $rowsTotal,
                'rows_ok'    => $rowsOk,
                'rows_error' => $rowsError,
                'error_log'  => empty($errorLog) ? null : $errorLog,
            ]);

        } catch (Throwable $e) {
            $this->import->update([
                'status'    => 'failed',
                'error_log' => [['row' => 0, 'error' => $e->getMessage()]],
            ]);
        }
    }

    // ── Helpers ──────────────────────────────────────────────────────

    /**
     * Convierte el mapeo guardado { campo => índice } al formato interno { campo => índice|null }.
     */
    private function resolveMapping(?array $savedMapping, array $rows): ?array
    {
        $defaults = ['barcode' => null, 'name' => null, 'desc' => null,
                     'price_ars' => null, 'price_usd' => null, 'currency' => null];

        if ($savedMapping) {
            return array_merge($defaults, $savedMapping);
        }

        // Fallback: intentar detección por nombre de columna
        if (empty($rows)) {
            return null;
        }

        return null;
    }

    private function parseDecimal(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        $clean = preg_replace('/[^\d,.]/', '', (string) $value);
        $clean = str_replace(',', '.', $clean);
        $float = (float) $clean;
        return $float > 0 ? $float : null;
    }
}
