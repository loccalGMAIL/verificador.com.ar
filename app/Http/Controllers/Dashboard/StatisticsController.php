<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\PageView;
use App\Models\ProductSearch;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StatisticsController extends Controller
{
    public function __invoke(Request $request): View
    {
        $store  = auth()->user()->store;
        $period = (int) $request->get('period', 30);
        $period = in_array($period, [7, 30, 90]) ? $period : 30;
        $since  = now()->subDays($period - 1)->startOfDay();

        $branches  = $store->branches()->where('active', true)->orderBy('name')->get();
        $branchIds = $branches->pluck('id')->all();
        $qrPaths   = $branches->map(fn ($b) => '/v/' . $b->qr_token)->all();

        // ── 1. Tendencias diarias ─────────────────────────────────────────────
        $rawVisits = $qrPaths
            ? PageView::whereIn('path', $qrPaths)
                ->where('created_at', '>=', $since)
                ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
                ->groupBy('day')
                ->pluck('total', 'day')
                ->toArray()
            : [];

        $rawSearches = $branchIds
            ? ProductSearch::whereIn('branch_id', $branchIds)
                ->where('created_at', '>=', $since)
                ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
                ->groupBy('day')
                ->pluck('total', 'day')
                ->toArray()
            : [];

        $labels = $visitsData = $searchesData = [];
        for ($i = $period - 1; $i >= 0; $i--) {
            $day            = now()->subDays($i)->format('Y-m-d');
            $labels[]       = now()->subDays($i)->locale('es')->isoFormat('D MMM');
            $visitsData[]   = (int) ($rawVisits[$day] ?? 0);
            $searchesData[] = (int) ($rawSearches[$day] ?? 0);
        }

        // ── 2. Stats por sucursal ─────────────────────────────────────────────
        $visitsByPath = $qrPaths
            ? PageView::whereIn('path', $qrPaths)
                ->where('created_at', '>=', $since)
                ->selectRaw('path, COUNT(*) as visits')
                ->groupBy('path')
                ->pluck('visits', 'path')
            : collect();

        $searchesByBranch = $branchIds
            ? ProductSearch::whereIn('branch_id', $branchIds)
                ->where('created_at', '>=', $since)
                ->selectRaw('branch_id, COUNT(*) as total, SUM(found) as found')
                ->groupBy('branch_id')
                ->get()
                ->keyBy('branch_id')
            : collect();

        $branchStats = $branches->map(function ($b) use ($visitsByPath, $searchesByBranch) {
            $s     = $searchesByBranch[$b->id] ?? null;
            $total = (int) ($s->total ?? 0);
            $found = (int) ($s->found ?? 0);

            return [
                'name'     => $b->name,
                'visits'   => (int) ($visitsByPath['/v/' . $b->qr_token] ?? 0),
                'total'    => $total,
                'found'    => $found,
                'hit_rate' => $total > 0 ? round($found / $total * 100) : null,
            ];
        });

        // ── 3. Horas pico (hora local Argentina) ──────────────────────────────
        $rawHours = $branchIds
            ? ProductSearch::whereIn('branch_id', $branchIds)
                ->where('created_at', '>=', $since)
                ->selectRaw('HOUR(CONVERT_TZ(created_at, "+00:00", "-03:00")) as hour, COUNT(*) as total')
                ->groupBy('hour')
                ->pluck('total', 'hour')
                ->toArray()
            : [];

        $hourLabels = array_map(fn ($h) => "{$h}h", range(0, 23));
        $hourData   = array_map(fn ($h) => (int) ($rawHours[$h] ?? 0), range(0, 23));

        // ── 4. Búsquedas no encontradas ───────────────────────────────────────
        $notFound = $branchIds
            ? ProductSearch::whereIn('branch_id', $branchIds)
                ->where('created_at', '>=', $since)
                ->where('found', false)
                ->selectRaw('barcode, COUNT(*) as total')
                ->groupBy('barcode')
                ->orderByDesc('total')
                ->limit(20)
                ->get()
            : collect();

        return view('dashboard.statistics', compact(
            'period', 'labels', 'visitsData', 'searchesData',
            'branchStats', 'hourLabels', 'hourData', 'notFound'
        ));
    }
}
