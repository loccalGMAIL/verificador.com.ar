<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessProductImport;
use App\Models\ImportProfile;
use App\Models\ProductImport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ProductImportController extends Controller
{
    /**
     * Campos base a los que se puede mapear una columna del CSV.
     * Los campos de precio por lista se generan dinámicamente en buildFields().
     */
    private const BASE_FIELDS = [
        'barcode'  => 'Código de barras *',
        'name'     => 'Nombre *',
        'desc'     => 'Descripción',
        'currency' => 'Moneda por defecto',
    ];

    /** Historial de imports + formulario de carga (Paso 1) */
    public function index(): View
    {
        $store   = auth()->user()->store;
        $imports = $store->productImports()
            ->with(['user', 'importProfile'])
            ->latest()
            ->paginate(15);

        $importProfiles = $store->importProfiles()->latest()->get();

        return view('dashboard.products.import', compact('imports', 'importProfiles'));
    }

    /**
     * Paso 1: recibir el archivo.
     * Si se elige un perfil guardado, aplica el mapeo y despacha el job directamente.
     * Si no, redirige al paso de mapeo manual.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'file'              => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
            'import_profile_id' => ['nullable', 'integer', 'exists:import_profiles,id'],
        ]);

        $store = auth()->user()->store;
        $sub   = $store->subscription;

        // Verificar límite de productos
        if (! $sub?->hasFullAccess() && $sub?->plan?->max_products !== null) {
            $count = $store->products()->where('active', true)->count();
            if ($count >= $sub->plan->max_products) {
                return redirect()->route('dashboard.products.import.index')
                    ->with('limit_reached', "Alcanzaste el límite de {$sub->plan->max_products} productos. Actualizá tu plan para importar más.");
            }
        }

        $file   = $request->file('file');
        $stored = $file->store("imports/{$store->id}", 'local');

        $profileId = $request->input('import_profile_id') ?: null;

        // Si se eligió un perfil, aplicar su mapeo y despachar directamente
        if ($profileId) {
            $profile = ImportProfile::find($profileId);

            if ($profile && $profile->store_id === $store->id) {
                $headers  = $this->readHeaders($stored);
                $fieldMap = $profile->resolveMapping($headers);

                if (isset($fieldMap['barcode']) && $fieldMap['barcode'] !== null
                    && isset($fieldMap['name']) && $fieldMap['name'] !== null) {

                    $import = ProductImport::create([
                        'store_id'          => $store->id,
                        'user_id'           => auth()->id(),
                        'file_name'         => $stored,
                        'mapping'           => $fieldMap,
                        'import_profile_id' => $profileId,
                        'status'            => 'pending',
                    ]);

                    ProcessProductImport::dispatch($import);

                    return redirect()->route('dashboard.products.import.index')
                        ->with('success', "Importación iniciada con el perfil \"{$profile->name}\".");
                }
            }
        }

        // Sin perfil (o perfil inválido) → paso de mapeo manual
        $import = ProductImport::create([
            'store_id'          => $store->id,
            'user_id'           => auth()->id(),
            'file_name'         => $stored,
            'import_profile_id' => $profileId,
            'status'            => 'pending_mapping',
        ]);

        return redirect()->route('dashboard.products.import.mapping', $import);
    }

    /** Paso 2: mostrar la página de mapeo de columnas */
    public function showMapping(ProductImport $import): View
    {
        $this->authorizeImport($import);

        $store      = auth()->user()->store;
        $priceLists = $store->priceLists()->where('active', true)->get();

        $headers = $this->readHeaders($import->file_name);
        $fields  = $this->buildFields($priceLists);
        $autoMap = $this->autoDetectMapping($headers, $priceLists);

        return view('dashboard.products.import-mapping',
            compact('import', 'headers', 'autoMap', 'fields', 'priceLists'));
    }

    /** Paso 2: guardar el mapeo y disparar el job */
    public function storeMapping(Request $request, ProductImport $import): RedirectResponse
    {
        $this->authorizeImport($import);

        $request->validate([
            'mapping'          => ['required', 'array'],
            'save_as_profile'  => ['sometimes', 'boolean'],
            'profile_name'     => ['nullable', 'string', 'max:100'],
        ]);

        $mapping = $request->input('mapping', []);
        $mapped  = array_values(array_filter($mapping, fn ($v) => $v !== ''));

        if (! in_array('barcode', $mapped)) {
            return back()->withErrors(['mapping' => 'Debés mapear al menos la columna "Código de barras".']);
        }
        if (! in_array('name', $mapped)) {
            return back()->withErrors(['mapping' => 'Debés mapear al menos la columna "Nombre".']);
        }

        // Invertir: { campo => índice_columna }
        $fieldMap = [];
        foreach ($mapping as $colIndex => $field) {
            if ($field !== '') {
                $fieldMap[$field] = (int) $colIndex;
            }
        }

        // Guardar como perfil si se solicitó
        if ($request->boolean('save_as_profile') && $request->filled('profile_name')) {
            $headers        = $this->readHeaders($import->file_name);
            $headerMapping  = [];

            foreach ($fieldMap as $field => $colIndex) {
                if (isset($headers[$colIndex])) {
                    $headerMapping[$field] = $headers[$colIndex];
                }
            }

            ImportProfile::create([
                'store_id'       => auth()->user()->store_id,
                'name'           => $request->input('profile_name'),
                'header_mapping' => $headerMapping,
            ]);
        }

        $import->update([
            'mapping' => $fieldMap,
            'status'  => 'pending',
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

    /**
     * Genera los campos disponibles para el mapeo, incluyendo campos de precio
     * dinámicos para cada lista de precios activa del comercio.
     *
     * Campos generados: price_list_{id}_ars, price_list_{id}_usd
     */
    private function buildFields(\Illuminate\Support\Collection $priceLists): array
    {
        $fields = self::BASE_FIELDS;

        foreach ($priceLists as $list) {
            $fields["price_list_{$list->id}_ars"] = "Precio ARS — {$list->name}";
            $fields["price_list_{$list->id}_usd"] = "Precio USD — {$list->name}";
        }

        return $fields;
    }

    /**
     * Auto-detecta el mapeo de columnas por nombre de encabezado.
     * Incluye auto-detección de columnas de precio por lista.
     */
    private function autoDetectMapping(array $headers, \Illuminate\Support\Collection $priceLists): array
    {
        // Patrones base
        $patterns = [
            'barcode'  => ['codigo_barras', 'barcode', 'codigo', 'ean', 'gtin'],
            'name'     => ['nombre', 'name', 'producto', 'descripcion_corta'],
            'desc'     => ['descripcion', 'description', 'desc', 'detalle'],
            'currency' => ['moneda_default', 'moneda', 'currency', 'divisa'],
        ];

        // Añadir patrones para cada lista de precios
        foreach ($priceLists as $list) {
            $listSlug = strtolower(preg_replace('/[^a-z0-9]/i', '_', $list->name));

            $patterns["price_list_{$list->id}_ars"] = [
                "precio_ars_{$listSlug}",
                "precio_{$listSlug}_ars",
                "precio_{$listSlug}",
                "price_{$listSlug}_ars",
                'precio_ars',
                'price_ars',
                'ars',
                'precio_pesos',
                'precio',
            ];
            $patterns["price_list_{$list->id}_usd"] = [
                "precio_usd_{$listSlug}",
                "precio_{$listSlug}_usd",
                "price_{$listSlug}_usd",
                'precio_usd',
                'price_usd',
                'usd',
                'precio_dolar',
            ];
        }

        $map  = [];
        $used = []; // evitar asignar el mismo campo a dos columnas

        foreach ($headers as $index => $header) {
            $normalized = strtolower(trim((string) $header));
            $map[$index] = '';

            foreach ($patterns as $field => $aliases) {
                if (in_array($normalized, $aliases, true) && ! in_array($field, $used, true)) {
                    $map[$index] = $field;
                    $used[]      = $field;
                    break;
                }
            }
        }

        return $map;
    }
}
