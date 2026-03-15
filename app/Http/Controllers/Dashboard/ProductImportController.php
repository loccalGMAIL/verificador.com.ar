<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessProductImport;
use App\Models\ProductImport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ProductImportController extends Controller
{
    /** Historial de imports + formulario de carga */
    public function index(): View
    {
        $imports = auth()->user()->store
            ->productImports()
            ->with('user')
            ->latest()
            ->paginate(15);

        return view('dashboard.products.import', compact('imports'));
    }

    /** Procesar archivo subido */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ]);

        $store = auth()->user()->store;
        $sub   = $store->subscription;

        // Verificar que el plan permita más productos antes de importar
        if (! $sub?->hasFullAccess() && $sub?->plan?->max_products !== null) {
            $count = $store->products()->where('active', true)->count();
            if ($count >= $sub->plan->max_products) {
                return redirect()->route('dashboard.products.import.index')
                    ->with('limit_reached', "Alcanzaste el límite de {$sub->plan->max_products} productos. Actualizá tu plan para importar más.");
            }
        }
        $file   = $request->file('file');
        $stored = $file->store("imports/{$store->id}", 'local');

        $import = ProductImport::create([
            'store_id'  => $store->id,
            'user_id'   => auth()->id(),
            'file_name' => $stored,
            'status'    => 'pending',
        ]);

        ProcessProductImport::dispatch($import);

        return redirect()->route('dashboard.products.import.index')
            ->with('success', 'Archivo recibido. El procesamiento comenzará en breve.');
    }

    /** Descarga la plantilla CSV de ejemplo */
    public function template(): Response
    {
        $csv  = "\xEF\xBB\xBF"; // BOM UTF-8 para Excel
        $csv .= "codigo_barras,nombre,descripcion,precio_ars,precio_usd,moneda_default\n";
        $csv .= "7790001234567,Leche La Serenísima 1L,Entera larga vida,1250.50,,ARS\n";
        $csv .= "7790009876543,Aceite Cocinero 900ml,,980.00,,ARS\n";
        $csv .= "7790004567890,Queso Cremoso,Por kilo,2500.00,2.50,ARS\n";

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="plantilla_productos.csv"',
        ]);
    }
}
