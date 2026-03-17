<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $store  = auth()->user()->store;
        $search = $request->get('q');
        $status = $request->get('status');

        $products = $store->products()
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('barcode', 'like', "%{$search}%"))
            ->when($status !== null && $status !== '', fn ($q) => $q->where('active', (bool) $status))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $subscription = $store->subscription;
        $productLimit = $subscription?->plan?->max_products;
        $productCount = $store->products()->where('active', true)->count();

        return view('dashboard.products.index', compact(
            'products', 'search', 'status', 'productLimit', 'productCount'
        ));
    }

    public function create(): View
    {
        $this->checkProductLimit();
        return view('dashboard.products.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->checkProductLimit();

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'barcode'     => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price'       => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'active'      => ['sometimes', 'boolean'],
            'image'       => ['nullable', 'image', 'max:2048'],
        ]);

        $storeId = auth()->user()->store_id;

        // Verificar barcode único en este comercio
        $exists = Product::where('store_id', $storeId)->where('barcode', $data['barcode'])->exists();
        if ($exists) {
            return back()->withInput()->withErrors(['barcode' => 'Ya existe un producto con ese código de barras.']);
        }

        $data['store_id'] = $storeId;
        $data['active']   = $request->boolean('active', true);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')
                ->store("products/{$storeId}", 'public');
        }

        $product = Product::create($data);

        return redirect()->route('dashboard.products.index')
            ->with('success', 'Producto creado correctamente.');
    }

    public function edit(Product $product): View
    {
        $this->authorizeProduct($product);
        return view('dashboard.products.edit', compact('product'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->authorizeProduct($product);

        $storeId = auth()->user()->store_id;

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'barcode'     => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price'       => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'active'      => ['sometimes', 'boolean'],
            'image'       => ['nullable', 'image', 'max:2048'],
        ]);

        // Verificar barcode único (excluyendo este producto)
        $exists = Product::where('store_id', $storeId)
            ->where('barcode', $data['barcode'])
            ->where('id', '!=', $product->id)
            ->exists();
        if ($exists) {
            return back()->withInput()->withErrors(['barcode' => 'Ya existe otro producto con ese código de barras.']);
        }

        $data['active'] = $request->boolean('active', true);

        if ($request->hasFile('image')) {
            // Eliminar imagen anterior
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $data['image_path'] = $request->file('image')
                ->store("products/{$storeId}", 'public');
        }

        $product->update($data);

        return redirect()->route('dashboard.products.index')
            ->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorizeProduct($product);

        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->delete();

        return redirect()->route('dashboard.products.index')
            ->with('success', 'Producto eliminado.');
    }

    // ----------------------------------------------------------------

    private function authorizeProduct(Product $product): void
    {
        abort_if($product->store_id !== auth()->user()->store_id, 403);
    }

    private function checkProductLimit(): void
    {
        $store = auth()->user()->store;
        $sub   = $store->subscription;

        // Trial = acceso total sin límites
        if ($sub?->hasFullAccess()) {
            return;
        }

        if ($sub && $sub->plan && $sub->plan->max_products !== null) {
            $count = $store->products()->where('active', true)->count();
            if ($count >= $sub->plan->max_products) {
                throw new \Illuminate\Http\Exceptions\HttpResponseException(
                    redirect()->route('dashboard.products.index')
                        ->with('limit_reached', "Alcanzaste el límite de {$sub->plan->max_products} productos de tu plan. Actualizá tu plan para agregar más.")
                );
            }
        }
    }
}
