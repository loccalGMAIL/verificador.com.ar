<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\PageView;
use App\Models\ProductSearch;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $store    = auth()->user()->store;
        $branches = $store?->branches()->where('active', true)->orderBy('name')->get() ?? collect();

        // Visitas al QR por sucursal (path = /v/{qr_token})
        $branchVisits = collect();
        if ($branches->isNotEmpty()) {
            $paths = $branches->map(fn($b) => '/v/' . $b->qr_token)->all();
            $visitsByPath = PageView::whereIn('path', $paths)
                ->selectRaw('path, count(*) as visits')
                ->groupBy('path')
                ->pluck('visits', 'path');

            $branchVisits = $branches->map(fn($b) => [
                'name'   => $b->name,
                'visits' => (int) ($visitsByPath['/v/' . $b->qr_token] ?? 0),
            ]);
        }

        // Top 5 productos más buscados (solo encontrados)
        $branchIds   = $branches->pluck('id')->all();
        $topProducts = collect();
        if ($branchIds) {
            $topProducts = ProductSearch::whereIn('branch_id', $branchIds)
                ->where('found', true)
                ->selectRaw('product_id, count(*) as searches')
                ->groupBy('product_id')
                ->orderByDesc('searches')
                ->limit(5)
                ->with('product:id,name')
                ->get();
        }

        return view('dashboard.home', compact('branches', 'branchVisits', 'topProducts'));
    }
}
