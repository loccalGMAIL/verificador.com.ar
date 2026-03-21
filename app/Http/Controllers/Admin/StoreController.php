<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StoreController extends Controller
{
    public function index(): View
    {
        $stores = Store::with(['subscription.plan', 'users'])
            ->withCount(['products', 'branches'])
            ->latest()
            ->paginate(20);

        return view('admin.stores.index', compact('stores'));
    }

    public function show(Store $store): View
    {
        $store->load([
            'users',
            'branches',
            'products',
            'subscription.plan',
            'productImports' => fn ($q) => $q->latest()->limit(10),
        ]);

        $store->loadCount(['products', 'branches', 'users']);

        return view('admin.stores.show', compact('store'));
    }

    public function suspend(Store $store): RedirectResponse
    {
        $store->update(['status' => 'suspended']);

        return back()->with('success', "Comercio \"{$store->name}\" suspendido.");
    }

    public function reactivate(Store $store): RedirectResponse
    {
        $store->update(['status' => 'active']);

        return back()->with('success', "Comercio \"{$store->name}\" reactivado.");
    }

    public function destroy(Store $store): RedirectResponse
    {
        $name = $store->name;

        DB::transaction(function () use ($store) {
            // Desasociar usuarios (no tienen cascade en FK)
            $store->users()->update(['store_id' => null]);

            // El resto (branches, products, subscriptions, price_lists,
            // product_imports, import_profiles) se borra en cascada por la DB.
            $store->delete();
        });

        return redirect()->route('admin.stores.index')
            ->with('success', "Comercio \"{$name}\" eliminado.");
    }
}
