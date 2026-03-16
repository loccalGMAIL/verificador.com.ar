<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductPrice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $store    = auth()->user()->store;
        $search   = $request->get('q');
        $currency = $request->get('currency');
        $status   = $request->get('status');

        $products = $store->products()
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('barcode', 'like', "%{$search}%"))
            ->when($currency, fn ($q) => $q->where('currency_default', $currency))
            ->when($status !== null && $status !== '', fn ($q) => $q->where('active', (bool) $status))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $subscription = $store->subscription;
        $productLimit = $subscription?->plan?->max_products;
        $productCount = $store->products()->where('active', true)->count();

        return view('dashboard.products.index', compact(
            'products', 'search', 'currency', 'status', 'productLimit', 'productCount'
        ));
    }

    public function create(): View
    {
        $this->checkProductLimit();
        $priceLists = auth()->user()->store->priceLists()->where('active', true)->get();
        return view('dashboard.products.create', compact('priceLists'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->checkProductLimit();

        $data = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'barcode'          => ['required', 'string', 'max:50'],
            'description'      => ['nullable', 'string', 'max:1000'],
            'price_ars'        => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'price_usd'        => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'currency_default' => ['required', 'in:ARS,USD'],
            'active'           => ['sometimes', 'boolean'],
            'image'            => ['nullable', 'image', 'max:2048'],
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

        // Guardar precios por lista
        $this->savePricesForProduct($request, $product);

        return redirect()->route('dashboard.products.index')
            ->with('success', 'Producto creado correctamente.');
    }

    public function edit(Product $product): View
    {
        $this->authorizeProduct($product);
        $priceLists = auth()->user()->store->priceLists()->where('active', true)->get();
        $product->load('prices');
        return view('dashboard.products.edit', compact('product', 'priceLists'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->authorizeProduct($product);

        $storeId = auth()->user()->store_id;

        $data = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'barcode'          => ['required', 'string', 'max:50'],
            'description'      => ['nullable', 'string', 'max:1000'],
            'price_ars'        => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'price_usd'        => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'currency_default' => ['required', 'in:ARS,USD'],
            'active'           => ['sometimes', 'boolean'],
            'image'            => ['nullable', 'image', 'max:2048'],
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

        // Actualizar precios por lista
        $this->savePricesForProduct($request, $product);

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

    /**
     * Upsert de precios por lista a partir del request.
     * Espera un campo prices[{price_list_id}][price_ars|price_usd|currency_default].
     */
    private function savePricesForProduct(Request $request, Product $product): void
    {
        $pricesInput = $request->input('prices', []);
        $legacySync  = null; // datos de la lista default para sincronizar campos legacy

        foreach ($pricesInput as $priceListId => $row) {
            $priceListId = (int) $priceListId;

            if (empty($row['price_ars']) && empty($row['price_usd'])) {
                ProductPrice::where('product_id', $product->id)
                    ->where('price_list_id', $priceListId)
                    ->delete();
                continue;
            }

            $saved = ProductPrice::updateOrCreate(
                ['product_id' => $product->id, 'price_list_id' => $priceListId],
                [
                    'price_ars'        => $row['price_ars'] ?: null,
                    'price_usd'        => $row['price_usd'] ?: null,
                    'currency_default' => $row['currency_default'] ?? 'ARS',
                ]
            );

            // Guardar referencia a la lista default para sincronizar legacy
            if ($legacySync === null) {
                $legacySync = $saved;
            }
        }

        // Sincronizar campos legacy del producto con el primer precio guardado
        if ($legacySync) {
            $product->update([
                'price_ars'        => $legacySync->price_ars,
                'price_usd'        => $legacySync->price_usd,
                'currency_default' => $legacySync->currency_default,
            ]);
        }
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
