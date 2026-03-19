<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessProductImport;
use App\Models\ProductImport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductImportController extends Controller
{
    /** Historial de imports + formulario de carga */
    public function index(): View
    {
        $store   = auth()->user()->store;
        $imports = $store->productImports()
            ->with(['user'])
            ->latest()
            ->paginate(15);

        return view('dashboard.products.import', compact('imports', 'store'));
    }

    /**
     * Recibir el archivo, crear el ProductImport y despachar el job directamente.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
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

        $import = ProductImport::create([
            'store_id'  => $store->id,
            'user_id'   => auth()->id(),
            'file_name' => $stored,
            'status'    => 'pending',
        ]);

        return redirect()->route('dashboard.products.import.show', $import);
    }

    public function show(ProductImport $import): View
    {
        abort_if($import->store_id !== auth()->user()->store_id, 403);
        return view('dashboard.products.import-show', compact('import'));
    }

    public function process(ProductImport $import): JsonResponse
    {
        abort_if($import->store_id !== auth()->user()->store_id, 403);

        if ($import->status !== 'pending') {
            return response()->json(['ok' => false, 'message' => 'No está pendiente']);
        }

        // Liberar el lock de sesión para que los requests de polling no queden bloqueados
        session()->save();

        ProcessProductImport::dispatchSync($import);

        return response()->json(['ok' => true]);
    }

    public function progress(ProductImport $import): JsonResponse
    {
        abort_if($import->store_id !== auth()->user()->store_id, 403);
        $import->refresh();

        $pct = $import->rows_total > 0
            ? round(($import->rows_processed / $import->rows_total) * 100)
            : 0;

        return response()->json([
            'status'      => $import->status,
            'total'       => $import->rows_total,
            'processed'   => $import->rows_processed,
            'ok'          => $import->rows_ok,
            'errors'      => $import->rows_error,
            'percentage'  => $pct,
            'is_complete' => in_array($import->status, ['completed', 'failed', 'cancelled']),
        ]);
    }

    public function cancel(ProductImport $import): RedirectResponse
    {
        abort_if($import->store_id !== auth()->user()->store_id, 403);

        if ($import->status !== 'pending') {
            return back()->with('error', 'Solo se pueden cancelar importaciones pendientes.');
        }

        $import->update(['status' => 'cancelled']);

        return back()->with('success', 'Importación cancelada.');
    }

    /** Descarga la plantilla CSV de ejemplo */
    public function template(): Response
    {
        $store = auth()->user()->store;
        $colBarcode = $store->excel_col_barcode ?? 'codigo';
        $colName    = $store->excel_col_name    ?? 'nombre';
        $colPrice   = $store->excel_col_price   ?? 'precio';

        $csv  = "\xEF\xBB\xBF";
        $csv .= "{$colBarcode},{$colName},{$colPrice}\n";
        $csv .= "7790001234567,Leche La Serenísima 1L,1250.50\n";
        $csv .= "7790009876543,Aceite Cocinero 900ml,980.00\n";

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="plantilla_productos.csv"',
        ]);
    }
}
