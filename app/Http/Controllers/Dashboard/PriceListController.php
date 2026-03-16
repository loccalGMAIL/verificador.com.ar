<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\PriceList;
use App\Models\ProductPrice;
use App\Services\PriceCalculationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\View\View;

class PriceListController extends Controller
{
    public function __construct(
        private readonly PriceCalculationService $calculator
    ) {}

    public function index(): View
    {
        $store      = auth()->user()->store;
        $priceLists = $store->priceLists()
            ->withCount('productPrices')
            ->with('baseList')
            ->get();
        $sub        = $store->subscription;

        $limit = $sub?->hasFullAccess() ? null : $sub?->plan?->max_price_lists;

        return view('dashboard.price-lists.index', compact('priceLists', 'limit'));
    }

    public function create(): View
    {
        $this->checkPriceListLimit();

        // Listas manuales disponibles como base (no derivadas, de este comercio)
        $manualLists = auth()->user()->store
            ->priceLists()
            ->whereNull('base_price_list_id')
            ->get();

        return view('dashboard.price-lists.create', compact('manualLists'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->checkPriceListLimit();

        $data = $request->validate([
            'name'               => ['required', 'string', 'max:100'],
            'description'        => ['nullable', 'string', 'max:255'],
            'base_price_list_id' => ['nullable', 'integer', 'exists:price_lists,id'],
            'adjustment_pct'     => ['nullable', 'numeric', 'min:-99.99', 'max:999.99'],
        ]);

        $storeId = auth()->user()->store_id;

        // Validar que la lista base pertenece al mismo comercio
        if (! empty($data['base_price_list_id'])) {
            $base = PriceList::find($data['base_price_list_id']);
            abort_if($base?->store_id !== $storeId, 403);
        } else {
            $data['base_price_list_id'] = null;
            $data['adjustment_pct']     = null;
        }

        $data['store_id']   = $storeId;
        $data['is_default'] = false;
        $data['sort_order'] = PriceList::where('store_id', $storeId)->count();

        $priceList = PriceList::create($data);

        // Calcular precios iniciales si es derivada
        if ($priceList->isCalculated()) {
            $this->calculator->recalculateForList($priceList);
        }

        return redirect()->route('dashboard.price-lists.index')
            ->with('success', "Lista \"{$priceList->name}\" creada correctamente.");
    }

    public function edit(PriceList $priceList): View
    {
        $this->authorizePriceList($priceList);

        if ($priceList->isCalculated()) {
            // Lista derivada: solo mostrar precios calculados, sin edición manual
            $products = auth()->user()->store
                ->products()
                ->where('active', true)
                ->orderBy('name')
                ->with([
                    'prices' => fn ($q) => $q->where('price_list_id', $priceList->id),
                ])
                ->paginate(30);

            $priceList->load('baseList');

            return view('dashboard.price-lists.edit', compact('priceList', 'products'));
        }

        // Lista manual: edición completa de precios
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

        $storeId = auth()->user()->store_id;

        $data = $request->validate([
            'name'               => ['required', 'string', 'max:100'],
            'description'        => ['nullable', 'string', 'max:255'],
            'active'             => ['sometimes', 'boolean'],
            'base_price_list_id' => ['nullable', 'integer', 'exists:price_lists,id'],
            'adjustment_pct'     => ['nullable', 'numeric', 'min:-99.99', 'max:999.99'],
        ]);

        // La lista por defecto no puede desactivarse
        if ($priceList->is_default) {
            unset($data['active']);
        } else {
            $data['active'] = $request->boolean('active', $priceList->active);
        }

        // Validar que la base pertenece al comercio y no sea ella misma
        if (! empty($data['base_price_list_id'])) {
            $base = PriceList::find($data['base_price_list_id']);
            abort_if($base?->store_id !== $storeId, 403);
            abort_if($base?->id === $priceList->id, 422);
        } else {
            $data['base_price_list_id'] = null;
            $data['adjustment_pct']     = null;
        }

        $pctChanged = (float) $data['adjustment_pct'] !== (float) $priceList->adjustment_pct
            || $data['base_price_list_id'] !== $priceList->base_price_list_id;

        $priceList->update($data);

        // Si cambió el porcentaje o la base, recalcular todos los precios
        if ($priceList->isCalculated() && $pctChanged) {
            $this->calculator->recalculateForList($priceList->fresh());
        }

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
     * Recalcula manualmente todos los precios de una lista derivada.
     * POST /dashboard/price-lists/{priceList}/recalculate
     */
    public function recalculate(PriceList $priceList): RedirectResponse
    {
        $this->authorizePriceList($priceList);

        if (! $priceList->isCalculated()) {
            return redirect()->route('dashboard.price-lists.edit', $priceList)
                ->with('error', 'Esta lista no es derivada.');
        }

        $this->calculator->recalculateForList($priceList);

        return redirect()->route('dashboard.price-lists.edit', $priceList)
            ->with('success', 'Precios recalculados correctamente.');
    }

    /**
     * Guarda (upsert) los precios de múltiples productos para esta lista.
     * Solo disponible para listas manuales.
     * POST /dashboard/price-lists/{priceList}/prices
     */
    public function savePrices(Request $request, PriceList $priceList): RedirectResponse
    {
        $this->authorizePriceList($priceList);

        if ($priceList->isCalculated()) {
            return redirect()->route('dashboard.price-lists.edit', $priceList)
                ->with('error', 'Los precios de una lista calculada no pueden editarse manualmente.');
        }

        $request->validate([
            'prices'                    => ['array'],
            'prices.*.product_id'       => ['required', 'integer'],
            'prices.*.price_ars'        => ['nullable', 'numeric', 'min:0'],
            'prices.*.price_usd'        => ['nullable', 'numeric', 'min:0'],
            'prices.*.currency_default' => ['required', 'in:ARS,USD'],
        ]);

        foreach ($request->input('prices', []) as $row) {
            $productId = (int) $row['product_id'];

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
