<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\ProductImport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Throwable;

class ProcessProductImport implements ShouldQueue
{
    use Queueable;

    public int $tries   = 1;
    public int $timeout = 300; // 5 minutos máximo

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

            // La primera fila es el encabezado
            $header = array_shift($rows);
            $header = array_map('trim', array_map('strtolower', $header));

            // Mapear columnas por nombre
            $col = [
                'barcode'   => array_search('codigo_barras', $header),
                'name'      => array_search('nombre', $header),
                'desc'      => array_search('descripcion', $header),
                'price_ars' => array_search('precio_ars', $header),
                'price_usd' => array_search('precio_usd', $header),
                'currency'  => array_search('moneda_default', $header),
            ];

            if ($col['barcode'] === false || $col['name'] === false) {
                $this->import->update([
                    'status'    => 'failed',
                    'error_log' => [['row' => 0, 'error' => 'El archivo no tiene las columnas requeridas: codigo_barras, nombre']],
                ]);
                return;
            }

            $rowsTotal = count($rows);
            $rowsOk    = 0;
            $rowsError = 0;
            $errorLog  = [];

            foreach ($rows as $index => $row) {
                $rowNum = $index + 2; // +2: 1 por el header, 1 para que sea 1-indexed

                // Saltar filas completamente vacías
                $filled = array_filter($row, fn ($v) => $v !== null && $v !== '');
                if (empty($filled)) {
                    $rowsTotal--;
                    continue;
                }

                $barcode   = trim((string) ($row[$col['barcode']] ?? ''));
                $name      = trim((string) ($row[$col['name']] ?? ''));
                $priceArs  = $col['price_ars'] !== false ? $this->parseDecimal($row[$col['price_ars']] ?? null) : null;
                $priceUsd  = $col['price_usd'] !== false ? $this->parseDecimal($row[$col['price_usd']] ?? null) : null;
                $currency  = strtoupper(trim((string) ($col['currency'] !== false ? ($row[$col['currency']] ?? 'ARS') : 'ARS')));
                $desc      = $col['desc'] !== false ? trim((string) ($row[$col['desc']] ?? '')) : null;

                // Validaciones básicas
                if ($barcode === '') {
                    $errorLog[] = ['row' => $rowNum, 'error' => 'Código de barras vacío'];
                    $rowsError++;
                    continue;
                }
                if ($name === '') {
                    $errorLog[] = ['row' => $rowNum, 'error' => "Fila {$rowNum}: nombre vacío (barcode: {$barcode})"];
                    $rowsError++;
                    continue;
                }
                if ($priceArs === null && $priceUsd === null) {
                    $errorLog[] = ['row' => $rowNum, 'error' => "Fila {$rowNum}: debe tener al menos un precio (barcode: {$barcode})"];
                    $rowsError++;
                    continue;
                }
                if (! in_array($currency, ['ARS', 'USD'])) {
                    $currency = 'ARS';
                }

                // Upsert: actualiza si existe, crea si no
                Product::updateOrCreate(
                    ['store_id' => $this->import->store_id, 'barcode' => $barcode],
                    [
                        'name'             => $name,
                        'description'      => $desc ?: null,
                        'price_ars'        => $priceArs,
                        'price_usd'        => $priceUsd,
                        'currency_default' => $currency,
                        'active'           => true,
                    ]
                );

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

    private function parseDecimal(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        // Normalizar separadores (1.250,50 → 1250.50)
        $clean = preg_replace('/[^\d,.]/', '', (string) $value);
        $clean = str_replace(',', '.', $clean);
        $float = (float) $clean;
        return $float > 0 ? $float : null;
    }
}
