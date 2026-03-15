<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
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
}
