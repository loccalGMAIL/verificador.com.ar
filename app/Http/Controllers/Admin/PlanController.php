<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlanController extends Controller
{
    public function index(): View
    {
        $plans = Plan::withCount('subscriptions')->orderBy('sort_order')->get();

        return view('admin.plans.index', compact('plans'));
    }

    public function create(): View
    {
        return view('admin.plans.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'         => ['required', 'string', 'max:100'],
            'price_usd'    => ['required', 'numeric', 'min:0'],
            'max_products' => ['nullable', 'integer', 'min:1'],
            'description'  => ['nullable', 'string', 'max:500'],
            'featured'     => ['boolean'],
            'active'       => ['boolean'],
            'sort_order'   => ['required', 'integer', 'min:0'],
        ]);

        $data['featured'] = $request->boolean('featured');
        $data['active']   = $request->boolean('active');

        Plan::create($data);

        return redirect()->route('admin.plans.index')
            ->with('success', "Plan \"{$data['name']}\" creado.");
    }

    public function edit(Plan $plan): View
    {
        return view('admin.plans.edit', compact('plan'));
    }

    public function update(Request $request, Plan $plan): RedirectResponse
    {
        $data = $request->validate([
            'name'         => ['required', 'string', 'max:100'],
            'price_usd'    => ['required', 'numeric', 'min:0'],
            'max_products' => ['nullable', 'integer', 'min:1'],
            'description'  => ['nullable', 'string', 'max:500'],
            'featured'     => ['boolean'],
            'active'       => ['boolean'],
            'sort_order'   => ['required', 'integer', 'min:0'],
        ]);

        $data['featured'] = $request->boolean('featured');
        $data['active']   = $request->boolean('active');

        $plan->update($data);

        return redirect()->route('admin.plans.index')
            ->with('success', "Plan \"{$plan->name}\" actualizado.");
    }

    public function destroy(Plan $plan): RedirectResponse
    {
        if ($plan->subscriptions()->exists()) {
            return back()->with('error', 'No se puede eliminar un plan con subscripciones activas.');
        }

        $plan->delete();

        return redirect()->route('admin.plans.index')
            ->with('success', "Plan \"{$plan->name}\" eliminado.");
    }
}
