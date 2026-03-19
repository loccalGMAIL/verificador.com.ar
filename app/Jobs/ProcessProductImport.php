<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\ProductImport;
use App\Models\Store;
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
        set_time_limit(300);
        ini_set('memory_limit', '256M');

        $this->import->refresh();
        if ($this->import->status === 'cancelled') {
            Storage::delete($this->import->file_name);
            return;
        }

        $this->import->update(['status' => 'processing']);

        try {
            $store = Store::find($this->import->store_id);

            $colBarcode = $this->normalizeHeader($store->excel_col_barcode ?? 'codigo');
            $colName    = $this->normalizeHeader($store->excel_col_name    ?? 'nombre');
            $colPrice   = $this->normalizeHeader($store->excel_col_price   ?? 'precio');

            $absolutePath = Storage::disk('local')->path($this->import->file_name);
            $spreadsheet  = IOFactory::load($absolutePath);
            $sheet        = $spreadsheet->getActiveSheet();
            // formatData = false: evita que PhpSpreadsheet transforme los valores de celda
            $rows = $sheet->toArray(null, true, false, false);

            // Primera fila = encabezados normalizados
            $headers = array_map(fn ($h) => $this->normalizeHeader((string) $h), $rows[0] ?? []);
            array_shift($rows);

            // Resolver índices de columnas por nombre
            $idxBarcode = array_search($colBarcode, $headers, true);
            $idxName    = array_search($colName, $headers, true);
            $idxPrice   = array_search($colPrice, $headers, true);

            if ($idxBarcode === false || $idxName === false) {
                $found = implode(', ', array_filter($headers));
                $this->import->update([
                    'status'    => 'failed',
                    'error_log' => [['row' => 0, 'error' => "No se encontraron las columnas '{$colBarcode}' y/o '{$colName}' en el archivo. Columnas detectadas: {$found}"]],
                ]);
                return;
            }

            $rowsTotal = count($rows);
            $rowsOk    = 0;
            $rowsError = 0;
            $errorLog  = [];
            $processed = 0;

            // Advertencia si la columna de precio no se encontró
            if ($idxPrice === false) {
                $detected = implode(' | ', array_filter($headers, fn($h) => $h !== ''));
                $errorLog[] = [
                    'row'   => 0,
                    'error' => "⚠ Columna de precio '{$colPrice}' no encontrada. Encabezados detectados: {$detected}",
                ];
            }

            foreach ($rows as $index => $row) {
                $rowNum = $index + 2;

                // Saltar filas vacías
                $filled = array_filter($row, fn ($v) => $v !== null && $v !== '');
                if (empty($filled)) {
                    $rowsTotal--;
                    continue;
                }

                $barcode = trim((string) ($row[$idxBarcode] ?? ''));
                $name    = trim((string) ($row[$idxName]    ?? ''));
                $price   = $idxPrice !== false ? $this->parseDecimal($row[$idxPrice] ?? null) : null;

                if ($barcode === '') {
                    $errorLog[] = ['row' => $rowNum, 'error' => 'Código de barras vacío'];
                    $rowsError++;
                    $processed++;
                    if ($processed % 10 === 0) {
                        $this->import->update(['rows_processed' => $processed]);
                    }
                    continue;
                }
                if ($name === '') {
                    $errorLog[] = ['row' => $rowNum, 'error' => "Nombre vacío (barcode: {$barcode})"];
                    $rowsError++;
                    $processed++;
                    if ($processed % 10 === 0) {
                        $this->import->update(['rows_processed' => $processed]);
                    }
                    continue;
                }

                Product::updateOrCreate(
                    ['store_id' => $this->import->store_id, 'barcode' => $barcode],
                    [
                        'name'   => $name,
                        'price'  => $price,
                        'active' => true,
                    ]
                );

                $rowsOk++;
                $processed++;
                if ($processed % 10 === 0) {
                    $this->import->update(['rows_processed' => $processed]);
                }
            }

            $this->import->update([
                'status'         => 'completed',
                'rows_total'     => $rowsTotal,
                'rows_processed' => $processed,
                'rows_ok'        => $rowsOk,
                'rows_error'     => $rowsError,
                'error_log'      => empty($errorLog) ? null : $errorLog,
            ]);

        } catch (Throwable $e) {
            $this->import->update([
                'status'    => 'failed',
                'error_log' => [['row' => 0, 'error' => $e->getMessage()]],
            ]);
        }
    }

    /**
     * Normaliza un encabezado para comparación:
     * - Quita caracteres de control e invisibles (zero-width space, BOM, etc.)
     * - Trim
     * - mb_strtolower para manejar acentos y ñ correctamente
     */
    private function normalizeHeader(string $value): string
    {
        // Eliminar caracteres de control y no imprimibles (incluye BOM y zero-width spaces)
        $clean = preg_replace('/[\x00-\x1F\x7F\xC2\xA0\x{200B}-\x{200D}\x{FEFF}]/u', '', $value);
        return mb_strtolower(trim($clean ?? $value));
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
