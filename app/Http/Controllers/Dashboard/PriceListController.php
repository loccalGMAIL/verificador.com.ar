<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\PriceList;
use App\Models\ProductPrice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\View\View;

class PriceListController extends Controller
{
    public function index(): View
    {
        $store      = auth()->user()->store;
        $priceLists = $store->priceLists()->withCount('productPrices')->get();
        $sub        = $store->subscription;

        $limit = $sub?->hasFullAccess() ? null : $sub?->plan?->max_price_lists;

        return view('dashboard.price-lists.index', compact('priceLists', 'limit'));
    }

    public function create(): View
    {
        $this->checkPriceListLimit();
        return view('dashboard.price-lists.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->checkPriceListLimit();

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $data['store_id']   = auth()->user()->store_id;
        $data['is_default'] = false;
        $data['sort_order'] = PriceList::where('store_id', $data['store_id'])->count();

        PriceList::create($data);

        return redirect()->route('dashboard.price-lists.index')
            ->with('success', "Lista \"{$data['name']}\" creada correctamente.");
    }

    public function edit(PriceList $priceList): View
    {
        $this->authorizePriceList($priceList);

        // Productos del store con su precio en esta lista (eager load)
        $store    = auth()->user()->store;
        $products = $store->products()
            ->where('active', true)
            ->orderBy('name')
            ->with(['prices' => fn ($q) => $q->where('price_list_id', $priceList->id)])
            ->paginate(30);

        return view('dashboard.price-lists.edit', compact('priceList', 'products'));
    }

    public function update(Request $request, PriceList $priceList): RedirectResponse
    {
        $this->authorizePriceList($priceList);

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'active'      => ['sometimes', 'boolean'],
        ]);

        // La lista por defecto no puede desactivarse
        if ($priceList->is_default) {
            unset($data['active']);
        } else {
            $data['active'] = $request->boolean('active', $priceList->active);
        }

        $priceList->update($data);

        return redirect()->route('dashboard.price-lists.edit', $priceList)
            ->with('success', 'Lista actualizada.');
    }

    public function destroy(PriceList $priceList): RedirectResponse
    {
        $this->authorizePriceList($priceList);

        if ($priceList->is_default) {
            return redirect()->route('dashboard.price-lists.index')
                ->with('error', 'No podés eliminar la lista de precios por defecto.');
        }

        $priceList->delete();

        return redirect()->route('dashboard.price-lists.index')
            ->with('success', "Lista \"{$priceList->name}\" eliminada.");
    }

    /**
     * Guarda (upsert) los precios de múltiples productos para esta lista.
     * POST /dashboard/price-lists/{priceList}/prices
     */
    public function savePrices(Request $request, PriceList $priceList): RedirectResponse
    {
        $this->authorizePriceList($priceList);

        $request->validate([
            'prices'                    => ['array'],
            'prices.*.product_id'       => ['required', 'integer'],
            'prices.*.price_ars'        => ['nullable', 'numeric', 'min:0'],
            'prices.*.price_usd'        => ['nullable', 'numeric', 'min:0'],
            'prices.*.currency_default' => ['required', 'in:ARS,USD'],
        ]);

        foreach ($request->input('prices', []) as $row) {
            $productId = (int) $row['product_id'];

            // Si ambos precios son vacíos, eliminar el registro (sin precio)
            if (empty($row['price_ars']) && empty($row['price_usd'])) {
                ProductPrice::where('product_id', $productId)
                    ->where('price_list_id', $priceList->id)
                    ->delete();
                continue;
            }

            ProductPrice::updateOrCreate(
                ['product_id' => $productId, 'price_list_id' => $priceList->id],
                [
                    'price_ars'        => $row['price_ars'] ?: null,
                    'price_usd'        => $row['price_usd'] ?: null,
                    'currency_default' => $row['currency_default'],
                ]
            );
        }

        return redirect()->route('dashboard.price-lists.edit', $priceList)
            ->with('success', 'Precios guardados correctamente.');
    }

    // ----------------------------------------------------------------

    private function authorizePriceList(PriceList $priceList): void
    {
        abort_if($priceList->store_id !== auth()->user()->store_id, 403);
    }

    private function checkPriceListLimit(): void
    {
        $store = auth()->user()->store;
        $sub   = $store->subscription;

        if ($sub?->hasFullAccess()) {
            return;
        }

        if ($sub && $sub->plan && $sub->plan->max_price_lists !== null) {
            $count = $store->priceLists()->count();
            if ($count >= $sub->plan->max_price_lists) {
                throw new HttpResponseException(
                    redirect()->route('dashboard.price-lists.index')
                        ->with('limit_reached', "Alcanzaste el límite de {$sub->plan->max_price_lists} lista(s) de tu plan. Actualizá tu plan para agregar más.")
                );
            }
        }
    }
}
