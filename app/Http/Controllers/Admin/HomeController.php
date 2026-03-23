<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PageView;
use App\Models\ProductSearch;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        // ── Estadísticas generales ────────────────────────────────────────
        $stats = [
            'stores'    => Store::count(),
            'users'     => User::where('role', '!=', 'admin')->count(),
            'trial'     => Subscription::where('status', 'trial')->count(),
            'active'    => Subscription::where('status', 'active')->count(),
            'suspended' => Subscription::where('status', 'suspended')->count(),
            'expiring'  => Subscription::where('status', 'trial')
                ->whereBetween('trial_ends_at', [now(), now()->addDays(7)])
                ->count(),
        ];

        // ── Estadísticas de visitas (últimos 30 días) ─────────────────────
        $since30 = now()->subDays(30)->startOfDay();
        $since14 = now()->subDays(13)->startOfDay(); // 14 días incluyendo hoy

        $visitStats = [
            'total_30d'  => PageView::where('created_at', '>=', $since30)->count(),
            'unique_30d' => PageView::where('created_at', '>=', $since30)
                ->whereNotNull('ip_hash')
                ->distinct('ip_hash')
                ->count('ip_hash'),
            'today'      => PageView::whereDate('created_at', today())->count(),
        ];

        // ── Estadísticas de búsquedas de precios (últimos 30 días) ────────
        $totalSearches30d = ProductSearch::where('created_at', '>=', $since30)->count();
        $foundSearches30d = ProductSearch::where('created_at', '>=', $since30)->where('found', true)->count();
        $hitRate = $totalSearches30d > 0 ? round($foundSearches30d / $totalSearches30d * 100) : null;

        // ── Visitas por día — últimos 14 días ─────────────────────────────
        $rawDaily = PageView::select(
                DB::raw('DATE(created_at) as day'),
                DB::raw('COUNT(*) as total')
            )
            ->where('created_at', '>=', $since14)
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day')
            ->toArray();

        $rawDailyScans = ProductSearch::select(
                DB::raw('DATE(created_at) as day'),
                DB::raw('COUNT(*) as total')
            )
            ->where('created_at', '>=', $since14)
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day')
            ->toArray();

        // Rellenar los días sin visitas con 0
        $dailyLabels    = [];
        $dailyData      = [];
        $dailyScanData  = [];
        for ($i = 13; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dailyLabels[]   = now()->subDays($i)->locale('es')->isoFormat('D MMM');
            $dailyData[]     = $rawDaily[$date] ?? 0;
            $dailyScanData[] = $rawDailyScans[$date] ?? 0;
        }

        // ── Top referrers (últimos 30 días) ───────────────────────────────
        $topReferrers = PageView::select(
                DB::raw('COALESCE(referer_domain, "direct") as domain'),
                'referer_category',
                DB::raw('COUNT(*) as total'),
                DB::raw('COUNT(DISTINCT ip_hash) as uniques')
            )
            ->where('created_at', '>=', $since30)
            ->groupBy('domain', 'referer_category')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // ── Distribución por plan (suscripciones activas) ─────────────────
        $planDistribution = Subscription::where('status', 'active')
            ->selectRaw('plan_id, count(*) as total')
            ->groupBy('plan_id')
            ->with('plan:id,name')
            ->orderByDesc('total')
            ->get();

        // ── Top comercios por actividad — últimos 30 días ─────────────────
        // Paso 1: obtener IDs y conteos (sin columnas extra en SELECT)
        $topStoreCounts = DB::table('product_searches')
            ->join('branches', 'branches.id', '=', 'product_searches.branch_id')
            ->where('product_searches.created_at', '>=', $since30)
            ->selectRaw('branches.store_id, COUNT(product_searches.id) as search_count')
            ->groupBy('branches.store_id')
            ->orderByDesc('search_count')
            ->limit(8)
            ->pluck('search_count', 'store_id');

        // Paso 2: cargar los stores y anexar el conteo
        $topActiveStores = Store::whereIn('id', $topStoreCounts->keys())
            ->with('subscription.plan')
            ->get()
            ->map(fn($s) => tap($s, fn($s) => $s->search_count = $topStoreCounts[$s->id] ?? 0))
            ->sortByDesc('search_count')
            ->values();

        // ── Últimos comercios registrados ─────────────────────────────────
        $recentStores = Store::with(['subscription.plan'])
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.home', compact(
            'stats',
            'recentStores',
            'visitStats',
            'totalSearches30d',
            'hitRate',
            'dailyLabels',
            'dailyData',
            'dailyScanData',
            'topReferrers',
            'planDistribution',
            'topActiveStores',
        ));
    }
}
