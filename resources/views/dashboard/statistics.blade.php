@extends('layouts.app')

@section('title', 'Estadísticas Avanzadas')
@section('page-title', 'Estadísticas Avanzadas')

@section('content')

{{-- ── Selector de período ──────────────────────────────────────────────────── --}}
<div class="flex items-center gap-2 mb-6">
    @foreach([7 => '7 días', 30 => '30 días', 90 => '90 días'] as $p => $label)
        <a href="{{ request()->fullUrlWithQuery(['period' => $p]) }}"
           class="px-4 py-1.5 rounded-full text-sm font-medium transition
                  {{ $period === $p
                      ? 'bg-blue-700 text-white shadow-sm'
                      : 'bg-white border border-slate-200 text-slate-600 hover:border-blue-400 hover:text-blue-700' }}">
            {{ $label }}
        </a>
    @endforeach
    <span class="ml-auto text-xs text-slate-400">Datos de tus sucursales activas</span>
</div>

@php $hasData = array_sum($visitsData) + array_sum($searchesData) > 0; @endphp

{{-- ── 1. Tendencias ───────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-xl border border-slate-200 p-5 mb-5">
    <h2 class="text-sm font-semibold text-slate-700 mb-4">
        <i class="fa-solid fa-chart-line text-blue-500 mr-1.5"></i>
        Tendencias — últimos {{ $period }} días
    </h2>

    @if($hasData)
        <div class="relative h-64">
            <canvas id="trendChart"></canvas>
        </div>
    @else
        <div class="flex flex-col items-center justify-center h-40 text-slate-400 gap-2">
            <i class="fa-solid fa-chart-line text-3xl"></i>
            <p class="text-sm">Sin datos para el período seleccionado.</p>
        </div>
    @endif
</div>

{{-- ── 2. Stats por sucursal ───────────────────────────────────────────────── --}}
<div class="bg-white rounded-xl border border-slate-200 p-5 mb-5">
    <h2 class="text-sm font-semibold text-slate-700 mb-4">
        <i class="fa-solid fa-store text-blue-500 mr-1.5"></i>
        Rendimiento por sucursal
    </h2>

    @if($branchStats->isEmpty())
        <p class="text-sm text-slate-400 text-center py-6">No tenés sucursales activas.</p>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="text-left font-medium text-slate-500 pb-2 pr-4">Sucursal</th>
                        <th class="text-right font-medium text-slate-500 pb-2 px-4">Visitas QR</th>
                        <th class="text-right font-medium text-slate-500 pb-2 px-4">Búsquedas</th>
                        <th class="text-right font-medium text-slate-500 pb-2 px-4">Encontradas</th>
                        <th class="text-right font-medium text-slate-500 pb-2 pl-4">Tasa éxito</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($branchStats as $row)
                    <tr class="hover:bg-slate-50/50">
                        <td class="py-2.5 pr-4 font-medium text-slate-800">{{ $row['name'] }}</td>
                        <td class="py-2.5 px-4 text-right text-slate-600">{{ number_format($row['visits']) }}</td>
                        <td class="py-2.5 px-4 text-right text-slate-600">{{ number_format($row['total']) }}</td>
                        <td class="py-2.5 px-4 text-right text-slate-600">{{ number_format($row['found']) }}</td>
                        <td class="py-2.5 pl-4 text-right">
                            @if($row['hit_rate'] !== null)
                                <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full
                                    {{ $row['hit_rate'] >= 70 ? 'bg-emerald-50 text-emerald-700' :
                                       ($row['hit_rate'] >= 40 ? 'bg-amber-50 text-amber-700' : 'bg-red-50 text-red-700') }}">
                                    {{ $row['hit_rate'] }}%
                                </span>
                            @else
                                <span class="text-slate-300">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                @if($branchStats->count() > 1)
                <tfoot>
                    <tr class="border-t border-slate-200 font-semibold text-slate-700">
                        <td class="pt-2.5 pr-4">Total</td>
                        <td class="pt-2.5 px-4 text-right">{{ number_format($branchStats->sum('visits')) }}</td>
                        <td class="pt-2.5 px-4 text-right">{{ number_format($branchStats->sum('total')) }}</td>
                        <td class="pt-2.5 px-4 text-right">{{ number_format($branchStats->sum('found')) }}</td>
                        <td class="pt-2.5 pl-4 text-right">
                            @php
                                $totalSearches = $branchStats->sum('total');
                                $totalFound    = $branchStats->sum('found');
                                $globalRate    = $totalSearches > 0 ? round($totalFound / $totalSearches * 100) : null;
                            @endphp
                            @if($globalRate !== null)
                                <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full
                                    {{ $globalRate >= 70 ? 'bg-emerald-50 text-emerald-700' :
                                       ($globalRate >= 40 ? 'bg-amber-50 text-amber-700' : 'bg-red-50 text-red-700') }}">
                                    {{ $globalRate }}%
                                </span>
                            @else
                                <span class="text-slate-300">—</span>
                            @endif
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    @endif
</div>

{{-- ── 3 + 4. Horas pico / No encontradas ─────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

    {{-- Horas pico --}}
    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <h2 class="text-sm font-semibold text-slate-700 mb-4">
            <i class="fa-solid fa-clock text-blue-500 mr-1.5"></i>
            Horas pico (hora argentina)
        </h2>

        @if(array_sum($hourData) > 0)
            <div class="relative h-52">
                <canvas id="hoursChart"></canvas>
            </div>
        @else
            <div class="flex flex-col items-center justify-center h-40 text-slate-400 gap-2">
                <i class="fa-solid fa-clock text-3xl"></i>
                <p class="text-sm">Sin búsquedas en el período.</p>
            </div>
        @endif
    </div>

    {{-- Búsquedas no encontradas --}}
    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <h2 class="text-sm font-semibold text-slate-700 mb-4">
            <i class="fa-solid fa-magnifying-glass-minus text-rose-500 mr-1.5"></i>
            Barcodes no encontrados
            <span class="ml-1 text-xs font-normal text-slate-400">(top 20)</span>
        </h2>

        @if($notFound->isEmpty())
            <div class="flex flex-col items-center justify-center h-40 text-slate-400 gap-2">
                <i class="fa-solid fa-circle-check text-3xl text-emerald-300"></i>
                <p class="text-sm">No hay búsquedas sin resultado.</p>
            </div>
        @else
            <div class="overflow-y-auto max-h-52 divide-y divide-slate-50">
                @foreach($notFound as $i => $item)
                <div class="flex items-center justify-between py-2 gap-3">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="text-xs text-slate-400 w-5 text-right flex-shrink-0">{{ $i + 1 }}</span>
                        <span class="font-mono text-sm text-slate-700 truncate">{{ $item->barcode }}</span>
                    </div>
                    <span class="flex-shrink-0 text-xs font-semibold bg-rose-50 text-rose-600 px-2 py-0.5 rounded-full">
                        {{ $item->total }}x
                    </span>
                </div>
                @endforeach
            </div>
        @endif
    </div>

</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script>
    Chart.defaults.font.family = 'Inter, sans-serif';
    Chart.defaults.color = '#64748b';

    // ── Gráfico de tendencias ──────────────────────────────────────────────────
    @if($hasData)
    const trendCtx = document.getElementById('trendChart');
    if (trendCtx) {
        new Chart(trendCtx, {
            data: {
                labels: @json($labels),
                datasets: [
                    {
                        type: 'bar',
                        label: 'Visitas QR',
                        data: @json($visitsData),
                        backgroundColor: 'rgba(59,130,246,0.15)',
                        borderColor: 'rgba(59,130,246,0.6)',
                        borderWidth: 1.5,
                        borderRadius: 4,
                        yAxisID: 'y',
                    },
                    {
                        type: 'line',
                        label: 'Búsquedas',
                        data: @json($searchesData),
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16,185,129,0.08)',
                        borderWidth: 2,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        tension: 0.3,
                        fill: true,
                        yAxisID: 'y',
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'top', labels: { boxWidth: 12, padding: 16, font: { size: 12 } } },
                    tooltip: { padding: 10 },
                },
                scales: {
                    x: { grid: { display: false }, ticks: { maxRotation: 45, font: { size: 11 } } },
                    y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { stepSize: 1, font: { size: 11 } } },
                },
            },
        });
    }
    @endif

    // ── Gráfico de horas pico ─────────────────────────────────────────────────
    @if(array_sum($hourData) > 0)
    const hoursCtx = document.getElementById('hoursChart');
    if (hoursCtx) {
        const hourData = @json($hourData);
        const maxVal   = Math.max(...hourData);
        new Chart(hoursCtx, {
            type: 'bar',
            data: {
                labels: @json($hourLabels),
                datasets: [{
                    label: 'Búsquedas',
                    data: hourData,
                    backgroundColor: hourData.map(v =>
                        v === maxVal && maxVal > 0 ? 'rgba(59,130,246,0.7)' : 'rgba(59,130,246,0.25)'
                    ),
                    borderColor: 'rgba(59,130,246,0.5)',
                    borderWidth: 1,
                    borderRadius: 3,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { title: (items) => 'Hora: ' + items[0].label } },
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 10 }, maxRotation: 0 } },
                    y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { stepSize: 1, font: { size: 11 } } },
                },
            },
        });
    }
    @endif
</script>
@endpush
