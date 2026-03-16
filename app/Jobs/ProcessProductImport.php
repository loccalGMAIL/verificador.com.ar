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

            $col = $this->resolveMapping($this->import->mapping);

            if ($col === null) {
                $this->import->update([
                    'status'    => 'failed',
                    'error_log' => [['row' => 0, 'error' => 'No se pudo determinar el mapeo de columnas.']],
                ]);
                return;
            }

            // Separar campos base de campos de precio por lista
            $baseCol       = $this->extractBaseColumns($col);
            $priceListCols = $this->extractPriceListColumns($col);

            // Resolver la lista destino por defecto (para los campos legacy price_ars/price_usd)
            $defaultList = PriceList::where('store_id', $this->import->store_id)
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

                $barcode  = $baseCol['barcode'] !== null ? trim((string) ($row[$baseCol['barcode']] ?? '')) : '';
                $name     = $baseCol['name'] !== null ? trim((string) ($row[$baseCol['name']] ?? '')) : '';
                $desc     = $baseCol['desc'] !== null ? trim((string) ($row[$baseCol['desc']] ?? '')) : null;
                $currency = 'ARS';

                if ($baseCol['currency'] !== null) {
                    $raw = strtoupper(trim((string) ($row[$baseCol['currency']] ?? '')));
                    $currency = in_array($raw, ['ARS', 'USD']) ? $raw : 'ARS';
                }

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

                // Upsert del producto (campos base — sin precio legacy al principio)
                $product = Product::updateOrCreate(
                    ['store_id' => $this->import->store_id, 'barcode' => $barcode],
                    [
                        'name'        => $name,
                        'description' => $desc ?: null,
                        'active'      => true,
                    ]
                );

                // ── Precios por lista (campos price_list_{id}_ars/usd) ──
                $firstPriceArs = null;
                $firstPriceUsd = null;
                $firstCurrency = $currency;

                foreach ($priceListCols as $listId => $priceCol) {
                    $priceArs = $priceCol['ars'] !== null
                        ? $this->parseDecimal($row[$priceCol['ars']] ?? null)
                        : null;
                    $priceUsd = $priceCol['usd'] !== null
                        ? $this->parseDecimal($row[$priceCol['usd']] ?? null)
                        : null;

                    if ($priceArs === null && $priceUsd === null) {
                        continue;
                    }

                    $list = PriceList::find($listId);
                    if (! $list || $list->store_id !== $this->import->store_id) {
                        continue;
                    }

                    // Listas calculadas no se editan manualmente
                    if ($list->isCalculated()) {
                        continue;
                    }

                    ProductPrice::updateOrCreate(
                        ['product_id' => $product->id, 'price_list_id' => $listId],
                        [
                            'price_ars'        => $priceArs,
                            'price_usd'        => $priceUsd,
                            'currency_default' => $currency,
                        ]
                    );

                    // Tomar el primer precio para sincronizar campos legacy
                    if ($firstPriceArs === null && $firstPriceUsd === null) {
                        $firstPriceArs = $priceArs;
                        $firstPriceUsd = $priceUsd;
                        $firstCurrency = $currency;
                    }
                }

                // Sincronizar campos legacy en el producto con el primer precio encontrado
                if ($firstPriceArs !== null || $firstPriceUsd !== null) {
                    $product->update([
                        'price_ars'        => $firstPriceArs,
                        'price_usd'        => $firstPriceUsd,
                        'currency_default' => $firstCurrency,
                    ]);
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
     * Convierte el mapeo guardado { campo => índice } al formato interno.
     * Devuelve null si no tiene las columnas mínimas requeridas.
     */
    private function resolveMapping(?array $savedMapping): ?array
    {
        if (empty($savedMapping)) {
            return null;
        }

        return $savedMapping;
    }

    /**
     * Extrae solo los campos base (barcode, name, desc, currency)
     * del mapping, devolviendo { campo => indice|null }.
     */
    private function extractBaseColumns(array $mapping): array
    {
        return [
            'barcode'  => $mapping['barcode']  ?? null,
            'name'     => $mapping['name']      ?? null,
            'desc'     => $mapping['desc']      ?? null,
            'currency' => $mapping['currency']  ?? null,
        ];
    }

    /**
     * Extrae los campos de precio por lista del mapping.
     * Devuelve { list_id => { ars => col_index|null, usd => col_index|null } }
     *
     * Soporta:
     *   price_list_{id}_ars / price_list_{id}_usd  → nuevos campos por lista
     */
    private function extractPriceListColumns(array $mapping): array
    {
        $result = [];

        foreach ($mapping as $field => $colIndex) {
            if (preg_match('/^price_list_(\d+)_(ars|usd)$/', $field, $m)) {
                $listId    = (int) $m[1];
                $currency  = $m[2]; // 'ars' o 'usd'

                if (! isset($result[$listId])) {
                    $result[$listId] = ['ars' => null, 'usd' => null];
                }
                $result[$listId][$currency] = $colIndex;
            }
        }

        return $result;
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
