<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessProductImport;
use App\Models\ProductImport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ProductImportController extends Controller
{
    /** Campos disponibles a los que se puede mapear una columna del CSV */
    private const FIELDS = [
        'barcode'   => 'Código de barras *',
        'name'      => 'Nombre *',
        'desc'      => 'Descripción',
        'price_ars' => 'Precio ARS',
        'price_usd' => 'Precio USD',
        'currency'  => 'Moneda por defecto',
    ];

    /** Historial de imports + formulario de carga (Paso 1) */
    public function index(): View
    {
        $store   = auth()->user()->store;
        $imports = $store->productImports()
            ->with(['user', 'priceList'])
            ->latest()
            ->paginate(15);

        $priceLists = $store->priceLists()->where('active', true)->get();

        return view('dashboard.products.import', compact('imports', 'priceLists'));
    }

    /** Paso 1: recibir el archivo y redirigir al paso de mapeo */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ]);

        $store  = auth()->user()->store;
        $sub    = $store->subscription;

        // Verificar límite de productos antes de importar
        if (! $sub?->hasFullAccess() && $sub?->plan?->max_products !== null) {
            $count = $store->products()->where('active', true)->count();
            if ($count >= $sub->plan->max_products) {
                return redirect()->route('dashboard.products.import.index')
                    ->with('limit_reached', "Alcanzaste el límite de {$sub->plan->max_products} productos. Actualizá tu plan para importar más.");
            }
        }

        $file   = $request->file('file');
        $stored = $file->store("imports/{$store->id}", 'local');

        // Crear registro con estado 'pending_mapping' — el job se dispara después del mapeo
        $import = ProductImport::create([
            'store_id'  => $store->id,
            'user_id'   => auth()->id(),
            'file_name' => $stored,
            'status'    => 'pending_mapping',
        ]);

        return redirect()->route('dashboard.products.import.mapping', $import);
    }

    /** Paso 2: mostrar la página de mapeo de columnas */
    public function showMapping(ProductImport $import): View
    {
        $this->authorizeImport($import);

        // Leer solo los headers del archivo
        $headers   = $this->readHeaders($import->file_name);
        $autoMap   = $this->autoDetectMapping($headers);
        $fields    = self::FIELDS;
        $priceLists = auth()->user()->store->priceLists()->where('active', true)->get();

        return view('dashboard.products.import-mapping',
            compact('import', 'headers', 'autoMap', 'fields', 'priceLists'));
    }

    /** Paso 2: guardar el mapeo y disparar el job */
    public function storeMapping(Request $request, ProductImport $import): RedirectResponse
    {
        $this->authorizeImport($import);

        $request->validate([
            'mapping'       => ['required', 'array'],
            'price_list_id' => ['nullable', 'integer', 'exists:price_lists,id'],
        ]);

        // Validar que al menos barcode y name estén mapeados
        $mapping = $request->input('mapping', []);
        $mapped  = array_values(array_filter($mapping, fn ($v) => $v !== ''));

        if (! in_array('barcode', $mapped)) {
            return back()->withErrors(['mapping' => 'Debés mapear al menos la columna "Código de barras".*']);
        }
        if (! in_array('name', $mapped)) {
            return back()->withErrors(['mapping' => 'Debés mapear al menos la columna "Nombre".*']);
        }

        // Invertir: { campo => índice_columna }
        $fieldMap = [];
        foreach ($mapping as $colIndex => $field) {
            if ($field !== '') {
                $fieldMap[$field] = (int) $colIndex;
            }
        }

        $import->update([
            'mapping'       => $fieldMap,
            'price_list_id' => $request->input('price_list_id') ?: null,
            'status'        => 'pending',
        ]);

        ProcessProductImport::dispatch($import);

        return redirect()->route('dashboard.products.import.index')
            ->with('success', 'Importación iniciada. Te avisamos cuando termine.');
    }

    /** Descarga la plantilla CSV de ejemplo */
    public function template(): Response
    {
        $csv  = "\xEF\xBB\xBF";
        $csv .= "codigo_barras,nombre,descripcion,precio_ars,precio_usd,moneda_default\n";
        $csv .= "7790001234567,Leche La Serenísima 1L,Entera larga vida,1250.50,,ARS\n";
        $csv .= "7790009876543,Aceite Cocinero 900ml,,980.00,,ARS\n";
        $csv .= "7790004567890,Queso Cremoso,Por kilo,2500.00,2.50,ARS\n";

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="plantilla_productos.csv"',
        ]);
    }

    // ── Helpers ──────────────────────────────────────────────────────

    private function authorizeImport(ProductImport $import): void
    {
        abort_if($import->store_id !== auth()->user()->store_id, 403);
    }

    /** Lee solo la primera fila del archivo para obtener los encabezados */
    private function readHeaders(string $storedPath): array
    {
        $absolutePath = Storage::disk('local')->path($storedPath);
        $spreadsheet  = IOFactory::load($absolutePath);
        $sheet        = $spreadsheet->getActiveSheet();
        $firstRow     = $sheet->toArray(null, true, true, false)[0] ?? [];

        return array_values(array_map('trim', $firstRow));
    }

    /** Intenta mapear automáticamente los encabezados detectados a los campos del sistema */
    private function autoDetectMapping(array $headers): array
    {
        $patterns = [
            'barcode'   => ['codigo_barras', 'barcode', 'codigo', 'ean', 'gtin'],
            'name'      => ['nombre', 'name', 'producto', 'descripcion_corta'],
            'desc'      => ['descripcion', 'description', 'desc', 'detalle'],
            'price_ars' => ['precio_ars', 'price_ars', 'ars', 'precio_pesos', 'precio'],
            'price_usd' => ['precio_usd', 'price_usd', 'usd', 'precio_dolar'],
            'currency'  => ['moneda_default', 'moneda', 'currency', 'divisa'],
        ];

        $map = [];
        foreach ($headers as $index => $header) {
            $normalized = strtolower(trim($header));
            foreach ($patterns as $field => $aliases) {
                if (in_array($normalized, $aliases, true)) {
                    $map[$index] = $field;
                    break;
                }
            }
            if (! isset($map[$index])) {
                $map[$index] = ''; // ignorar por defecto
            }
        }

        return $map;
    }
}
