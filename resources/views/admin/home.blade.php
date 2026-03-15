@extends('layouts.admin')

@section('title', 'Dashboard Admin')
@section('page-title', 'Dashboard')

@section('content')

{{-- ══════════════════════════════════════════════════════════════
     STATS GENERALES
════════════════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

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
     GRÁFICO DE VISITAS (14 días) + REFERRERS
════════════════════════════════════════════════════════════════ --}}
<div class="grid lg:grid-cols-3 gap-5 mb-6">

    {{-- Gráfico --}}
    <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 p-5">
        <h3 class="font-semibold text-slate-800 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-chart-bar text-sky-500 text-sm"></i>
            Visitas diarias — últimos 14 días
        </h3>
        <div class="relative h-48">
            <canvas id="visitsChart"></canvas>
        </div>
    </div>

    {{-- Top páginas --}}
    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <h3 class="font-semibold text-slate-800 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-file-lines text-slate-400 text-sm"></i>
            Páginas más visitadas <span class="text-xs font-normal text-slate-400">(30d)</span>
        </h3>
        @forelse($topPages as $page)
            <div class="flex items-center justify-between py-1.5 text-sm border-b border-slate-50 last:border-0">
                <span class="text-slate-600 font-mono text-xs truncate max-w-[130px]" title="{{ $page->path }}">
                    {{ $page->path }}
                </span>
                <span class="font-semibold text-slate-800 ml-2 tabular-nums">{{ number_format($page->total) }}</span>
            </div>
        @empty
            <p class="text-sm text-slate-400">Sin datos aún.</p>
        @endforelse
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

    {{-- Últimos comercios --}}
    <div class="bg-white rounded-xl border border-slate-200 p-5">
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

</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script>
(function () {
    const labels = @json($dailyLabels);
    const data   = @json($dailyData);

    const ctx = document.getElementById('visitsChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Visitas',
                data,
                backgroundColor: 'rgba(14, 165, 233, 0.18)',  // sky-500 suave
                borderColor:     'rgba(14, 165, 233, 0.85)',
                borderWidth: 1.5,
                borderRadius: 4,
                hoverBackgroundColor: 'rgba(14, 165, 233, 0.35)',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (ctx) => ` ${ctx.parsed.y} visita${ctx.parsed.y !== 1 ? 's' : ''}`,
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
                    ticks: {
                        font: { size: 11 },
                        color: '#94a3b8',
                        precision: 0,
                    },
                    grid: { color: '#f1f5f9' }
                }
            }
        }
    });
})();
</script>
@endpush
