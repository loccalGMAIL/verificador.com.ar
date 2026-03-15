<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PageView;
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
            'stores' => Store::count(),
            'users'  => User::where('role', '!=', 'admin')->count(),
            'trial'  => Subscription::where('status', 'trial')->count(),
            'active' => Subscription::where('status', 'active')->count(),
        ];

        $recentStores = Store::with(['subscription.plan'])
            ->latest()
            ->limit(10)
            ->get();

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

        // ── Visitas por día — últimos 14 días ─────────────────────────────
        $rawDaily = PageView::select(
                DB::raw('DATE(created_at) as day'),
                DB::raw('COUNT(*) as total')
            )
            ->where('created_at', '>=', $since14)
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day')  // ['2026-03-01' => 12, ...]
            ->toArray();

        // Rellenar los días sin visitas con 0
        $dailyLabels = [];
        $dailyData   = [];
        for ($i = 13; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dailyLabels[] = now()->subDays($i)->locale('es')->isoFormat('D MMM');
            $dailyData[]   = $rawDaily[$date] ?? 0;
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

        // ── Visitas por página (últimos 30 días) ──────────────────────────
        $topPages = PageView::select(
                'path',
                DB::raw('COUNT(*) as total')
            )
            ->where('created_at', '>=', $since30)
            ->groupBy('path')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        return view('admin.home', compact(
            'stats',
            'recentStores',
            'visitStats',
            'dailyLabels',
            'dailyData',
            'topReferrers',
            'topPages',
        ));
    }
}
