@extends('layouts.admin')

@section('title', 'Dashboard Admin')
@section('page-title', 'Dashboard')

@section('content')

{{-- ══════════════════════════════════════════════════════════════
     STATS GENERALES — FILA 1: Comercios y suscripciones
════════════════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-4">

    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm font-medium text-slate-500">Comercios</span>
            <span class="w-9 h-9 bg-blue-50 rounded-lg flex items-center justify-center">
                <i class="fa-solid fa-shop text-blue-600 text-sm"></i>
            </span>
        </div>
        <p class="text-3xl font-bold text-slate-900">{{ $stats['stores'] }}</p>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm font-medium text-slate-500">En trial</span>
            <span class="w-9 h-9 bg-amber-50 rounded-lg flex items-center justify-center">
                <i class="fa-solid fa-clock text-amber-600 text-sm"></i>
            </span>
        </div>
        <p class="text-3xl font-bold text-slate-900">{{ $stats['trial'] }}</p>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm font-medium text-slate-500">Activas</span>
            <span class="w-9 h-9 bg-emerald-50 rounded-lg flex items-center justify-center">
                <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
            </span>
        </div>
        <p class="text-3xl font-bold text-slate-900">{{ $stats['active'] }}</p>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm font-medium text-slate-500">Usuarios</span>
            <span class="w-9 h-9 bg-violet-50 rounded-lg flex items-center justify-center">
                <i class="fa-solid fa-users text-violet-600 text-sm"></i>
            </span>
        </div>
        <p class="text-3xl font-bold text-slate-900">{{ $stats['users'] }}</p>
    </div>

</div>

{{-- STATS GENERALES — FILA 2: Actividad y alertas --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm font-medium text-slate-500">Búsquedas (30d)</span>
            <span class="w-9 h-9 bg-sky-50 rounded-lg flex items-center justify-center">
                <i class="fa-solid fa-barcode text-sky-600 text-sm"></i>
            </span>
        </div>
        <p class="text-3xl font-bold text-slate-900">{{ number_format($totalSearches30d) }}</p>
        <p class="text-xs text-slate-400 mt-1">escaneos de precios</p>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm font-medium text-slate-500">Tasa de acierto</span>
            <span class="w-9 h-9 bg-teal-50 rounded-lg flex items-center justify-center">
                <i class="fa-solid fa-bullseye text-teal-600 text-sm"></i>
            </span>
        </div>
        @if($hitRate !== null)
            <p class="text-3xl font-bold {{ $hitRate >= 80 ? 'text-teal-600' : ($hitRate >= 50 ? 'text-amber-500' : 'text-red-500') }}">
                {{ $hitRate }}<span class="text-lg font-semibold">%</span>
            </p>
            <p class="text-xs text-slate-400 mt-1">productos encontrados</p>
        @else
            <p class="text-2xl font-bold text-slate-300">—</p>
            <p class="text-xs text-slate-400 mt-1">sin datos aún</p>
        @endif
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm font-medium text-slate-500">Trial x vencer</span>
            <span class="w-9 h-9 {{ $stats['expiring'] > 0 ? 'bg-orange-50' : 'bg-slate-50' }} rounded-lg flex items-center justify-center">
                <i class="fa-solid fa-triangle-exclamation {{ $stats['expiring'] > 0 ? 'text-orange-500' : 'text-slate-300' }} text-sm"></i>
            </span>
        </div>
        <p class="text-3xl font-bold {{ $stats['expiring'] > 0 ? 'text-orange-500' : 'text-slate-900' }}">
            {{ $stats['expiring'] }}
        </p>
        <p class="text-xs text-slate-400 mt-1">próximos 7 días</p>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm font-medium text-slate-500">Suspendidas</span>
            <span class="w-9 h-9 {{ $stats['suspended'] > 0 ? 'bg-red-50' : 'bg-slate-50' }} rounded-lg flex items-center justify-center">
                <i class="fa-solid fa-ban {{ $stats['suspended'] > 0 ? 'text-red-500' : 'text-slate-300' }} text-sm"></i>
            </span>
        </div>
        <p class="text-3xl font-bold {{ $stats['suspended'] > 0 ? 'text-red-500' : 'text-slate-900' }}">
            {{ $stats['suspended'] }}
        </p>
        <p class="text-xs text-slate-400 mt-1">suscripciones</p>
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════════
     STATS DE VISITAS
════════════════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-3 gap-4 mb-6">

    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm font-medium text-slate-500">Visitas hoy</span>
            <span class="w-9 h-9 bg-sky-50 rounded-lg flex items-center justify-center">
                <i class="fa-solid fa-eye text-sky-600 text-sm"></i>
            </span>
        </div>
        <p class="text-3xl font-bold text-slate-900">{{ number_format($visitStats['today']) }}</p>
        <p class="text-xs text-slate-400 mt-1">páginas vistas</p>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm font-medium text-slate-500">Visitas (30d)</span>
            <span class="w-9 h-9 bg-sky-50 rounded-lg flex items-center justify-center">
                <i class="fa-solid fa-chart-line text-sky-600 text-sm"></i>
            </span>
        </div>
        <p class="text-3xl font-bold text-slate-900">{{ number_format($visitStats['total_30d']) }}</p>
        <p class="text-xs text-slate-400 mt-1">páginas vistas</p>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm font-medium text-slate-500">Únicas (30d)</span>
            <span class="w-9 h-9 bg-sky-50 rounded-lg flex items-center justify-center">
                <i class="fa-solid fa-user-check text-sky-600 text-sm"></i>
            </span>
        </div>
        <p class="text-3xl font-bold text-slate-900">{{ number_format($visitStats['unique_30d']) }}</p>
        <p class="text-xs text-slate-400 mt-1">visitantes únicos (IP)</p>
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════════
     GRÁFICO DE VISITAS + BÚSQUEDAS (14 días) + DISTRIBUCIÓN POR PLAN
════════════════════════════════════════════════════════════════ --}}
<div class="grid lg:grid-cols-3 gap-5 mb-6">

    {{-- Gráfico (2 datasets) --}}
    <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-chart-bar text-sky-500 text-sm"></i>
                Actividad — últimos 14 días
            </h3>
            <div class="flex items-center gap-4 text-xs text-slate-400">
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-sm bg-sky-400 opacity-70 inline-block"></span> Visitas
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-0.5 bg-violet-500 inline-block"></span> Búsquedas
                </span>
            </div>
        </div>
        <div class="relative h-48">
            <canvas id="visitsChart"></canvas>
        </div>
    </div>

    {{-- Distribución por plan --}}
    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <h3 class="font-semibold text-slate-800 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-layer-group text-violet-400 text-sm"></i>
            Distribución por plan
            <span class="text-xs font-normal text-slate-400">(activas)</span>
        </h3>
        @if($planDistribution->isEmpty())
            <p class="text-sm text-slate-400">Sin suscripciones activas.</p>
        @else
            <div class="flex flex-col gap-3">
                @foreach($planDistribution as $row)
                @php
                    $maxPlan = $planDistribution->max('total');
                    $pct = $maxPlan > 0 ? round($row->total / $maxPlan * 100) : 0;
                @endphp
                <div>
                    <div class="flex justify-between text-xs mb-1">
                        <span class="font-medium text-slate-700">{{ $row->plan?->name ?? 'Sin plan' }}</span>
                        <span class="font-semibold text-violet-600">{{ $row->total }}</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-1.5">
                        <div class="bg-violet-400 h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════════
     TABLA DE REFERRERS + ÚLTIMOS COMERCIOS
════════════════════════════════════════════════════════════════ --}}
<div class="grid lg:grid-cols-2 gap-5 mb-6">

    {{-- Top referrers --}}
    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <h3 class="font-semibold text-slate-800 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-arrow-up-right-from-square text-slate-400 text-sm"></i>
            Origen del tráfico <span class="text-xs font-normal text-slate-400">(30d)</span>
        </h3>
        @if($topReferrers->isEmpty())
            <p class="text-sm text-slate-400">Sin datos aún.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 text-left">
                            <th class="pb-2 font-semibold text-slate-500 text-xs">Origen</th>
                            <th class="pb-2 font-semibold text-slate-500 text-xs text-right">Visitas</th>
                            <th class="pb-2 font-semibold text-slate-500 text-xs text-right">Únicos</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($topReferrers as $ref)
                        <tr>
                            <td class="py-2 flex items-center gap-2">
                                {{-- Ícono de categoría --}}
                                @php
                                    $cat = $ref->referer_category ?? 'direct';
                                    $icon = match($cat) {
                                        'search' => ['fa-magnifying-glass', 'text-blue-500', 'bg-blue-50'],
                                        'social' => ['fa-share-nodes', 'text-pink-500', 'bg-pink-50'],
                                        'direct' => ['fa-arrow-right', 'text-slate-500', 'bg-slate-100'],
                                        default  => ['fa-globe', 'text-violet-500', 'bg-violet-50'],
                                    };
                                @endphp
                                <span class="w-6 h-6 rounded {{ $icon[2] }} flex items-center justify-center flex-shrink-0">
                                    <i class="fa-solid {{ $icon[0] }} {{ $icon[1] }} text-[10px]"></i>
                                </span>
                                <span class="text-slate-700 truncate max-w-[140px]" title="{{ $ref->domain }}">
                                    {{ $ref->domain === 'direct' ? 'Directo' : $ref->domain }}
                                </span>
                            </td>
                            <td class="py-2 text-right font-semibold text-slate-800 tabular-nums">
                                {{ number_format($ref->total) }}
                            </td>
                            <td class="py-2 text-right text-slate-400 tabular-nums text-xs">
                                {{ number_format($ref->uniques) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Top comercios por actividad --}}
    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <h3 class="font-semibold text-slate-800 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-fire text-orange-400 text-sm"></i>
            Comercios más activos
            <span class="text-xs font-normal text-slate-400">(30d)</span>
        </h3>
        @if($topActiveStores->isEmpty())
            <p class="text-sm text-slate-400">Sin actividad registrada aún.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 text-left">
                            <th class="pb-2 font-semibold text-slate-500 text-xs">Comercio</th>
                            <th class="pb-2 font-semibold text-slate-500 text-xs">Plan</th>
                            <th class="pb-2 font-semibold text-slate-500 text-xs text-right">Búsquedas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($topActiveStores as $store)
                        <tr class="hover:bg-slate-50">
                            <td class="py-2 font-medium text-slate-800">
                                <a href="{{ route('admin.stores.show', $store) }}" class="hover:text-blue-600 transition">
                                    {{ $store->name }}
                                </a>
                            </td>
                            <td class="py-2 text-slate-500 text-xs">{{ $store->subscription?->plan?->name ?? '—' }}</td>
                            <td class="py-2 text-right font-semibold text-orange-500 tabular-nums">
                                {{ number_format($store->search_count) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════════
     ÚLTIMOS COMERCIOS REGISTRADOS
════════════════════════════════════════════════════════════════ --}}
<div class="bg-white rounded-xl border border-slate-200 p-5 mb-6">
    <h3 class="font-semibold text-slate-800 mb-4 flex items-center gap-2">
        <i class="fa-solid fa-shop text-slate-400 text-sm"></i>
        Últimos comercios registrados
    </h3>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 text-left">
                    <th class="pb-2 font-semibold text-slate-500 text-xs">Comercio</th>
                    <th class="pb-2 font-semibold text-slate-500 text-xs">Plan</th>
                    <th class="pb-2 font-semibold text-slate-500 text-xs">Estado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($recentStores as $store)
                <tr class="hover:bg-slate-50">
                    <td class="py-2 font-medium text-slate-800">
                        <a href="{{ route('admin.stores.show', $store) }}" class="hover:text-blue-600 transition">
                            {{ $store->name }}
                        </a>
                        <div class="text-xs text-slate-400">{{ $store->created_at->diffForHumans() }}</div>
                    </td>
                    <td class="py-2 text-slate-600 text-xs">{{ $store->subscription?->plan?->name ?? '—' }}</td>
                    <td class="py-2">
                        @php $status = $store->subscription?->status ?? 'none'; @endphp
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                            {{ $status === 'active' ? 'bg-emerald-100 text-emerald-700' : '' }}
                            {{ $status === 'trial' ? 'bg-amber-100 text-amber-700' : '' }}
                            {{ $status === 'suspended' ? 'bg-red-100 text-red-700' : '' }}
                            {{ $status === 'none' ? 'bg-slate-100 text-slate-600' : '' }}">
                            {{ match($status) {
                                'active'    => 'Activa',
                                'trial'     => 'Trial',
                                'suspended' => 'Suspendida',
                                'cancelled' => 'Cancelada',
                                default     => 'Sin sub.'
                            } }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="py-6 text-center text-slate-400">No hay comercios aún.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script>
(function () {
    const labels    = @json($dailyLabels);
    const visits    = @json($dailyData);
    const searches  = @json($dailyScanData);

    const ctx = document.getElementById('visitsChart');
    if (!ctx) return;

    new Chart(ctx, {
        data: {
            labels,
            datasets: [
                {
                    type: 'bar',
                    label: 'Visitas',
                    data: visits,
                    backgroundColor: 'rgba(14, 165, 233, 0.18)',
                    borderColor:     'rgba(14, 165, 233, 0.85)',
                    borderWidth: 1.5,
                    borderRadius: 4,
                    hoverBackgroundColor: 'rgba(14, 165, 233, 0.35)',
                    yAxisID: 'y',
                },
                {
                    type: 'line',
                    label: 'Búsquedas',
                    data: searches,
                    borderColor:     'rgba(139, 92, 246, 0.85)',
                    backgroundColor: 'rgba(139, 92, 246, 0.08)',
                    borderWidth: 2,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    tension: 0.3,
                    fill: true,
                    yAxisID: 'y',
                },
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (ctx) => ` ${ctx.dataset.label}: ${ctx.parsed.y}`,
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11 }, color: '#94a3b8' }
                },
                y: {
                    beginAtZero: true,
                    ticks: { font: { size: 11 }, color: '#94a3b8', precision: 0 },
                    grid: { color: '#f1f5f9' }
                }
            }
        }
    });
})();
</script>
@endpush
