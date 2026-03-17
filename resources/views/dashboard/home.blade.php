@extends('layouts.app')

@section('title', 'Inicio')
@section('page-title', 'Inicio')

@section('content')
@php
    $store = auth()->user()->store;
    $sub   = $store?->subscription;
@endphp

{{-- ══ FILA 1: Plan + Acciones rápidas ══ --}}
<div class="flex flex-col lg:flex-row gap-4 mb-6">

    {{-- Card Plan --}}
    <div class="bg-white rounded-xl border border-slate-200 p-5 flex-1">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm font-medium text-slate-500">Plan</span>
            <span class="w-9 h-9 bg-violet-50 rounded-lg flex items-center justify-center">
                <i class="fa-solid fa-credit-card text-violet-600 text-sm"></i>
            </span>
        </div>
        <p class="text-3xl font-bold text-slate-900">{{ $sub?->plan?->name ?? 'Sin plan' }}</p>
        <div class="flex items-center justify-between mt-1">
            <p class="text-xs {{ $sub?->isOnTrial() ? 'text-amber-500' : 'text-slate-400' }}">
                @if($sub?->isOnTrial())    Trial · {{ $sub->trialDaysRemaining() }} día(s) restantes
                @elseif($sub?->isActive()) Suscripción activa
                @else                     {{ ucfirst($sub?->status ?? 'sin suscripción') }}
                @endif
            </p>
            <a href="{{ route('dashboard.subscription') }}"
               class="text-xs text-violet-600 hover:text-violet-800 font-medium transition">
                Ver planes <i class="fa-solid fa-arrow-right ml-0.5"></i>
            </a>
        </div>
    </div>

    {{-- Acciones rápidas --}}
    <div class="lg:w-56 flex-shrink-0">
        <div class="bg-white rounded-xl border border-slate-200 p-5 h-full flex flex-col">
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Acciones rápidas</h3>
            <div class="space-y-2 flex-1">
                <a href="{{ route('dashboard.products.import.index') }}"
                   class="flex items-center gap-3 p-3 rounded-lg border border-slate-200
                          hover:border-blue-300 hover:bg-blue-50 transition group">
                    <i class="fa-solid fa-file-arrow-up text-blue-500 text-sm w-4 text-center"></i>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-slate-800 group-hover:text-blue-700">Importar Excel</p>
                        <p class="text-xs text-slate-400 truncate">Carga masiva</p>
                    </div>
                </a>
                <a href="{{ route('dashboard.branches.create') }}"
                   class="flex items-center gap-3 p-3 rounded-lg border border-slate-200
                          hover:border-emerald-300 hover:bg-emerald-50 transition group">
                    <i class="fa-solid fa-store text-emerald-600 text-sm w-4 text-center"></i>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-slate-800 group-hover:text-emerald-700">Nueva sucursal</p>
                        <p class="text-xs text-slate-400 truncate">Crear y generar QR</p>
                    </div>
                </a>
            </div>
        </div>
    </div>

</div>

{{-- ══ FILA 1.5: Uso del plan ══ --}}
@if($sub && !$sub->isExpired())
<div class="bg-white rounded-xl border border-slate-200 p-5 mb-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-semibold text-slate-700">Uso del plan</h3>
        @if($sub->isOnTrial())
            <span class="text-xs bg-amber-100 text-amber-700 font-medium px-2.5 py-0.5 rounded-full">
                Trial · acceso total
            </span>
        @else
            <a href="{{ route('dashboard.subscription') }}" class="text-xs text-slate-400 hover:text-blue-600 transition">
                {{ $sub->plan?->name }} <i class="fa-solid fa-arrow-right ml-1"></i>
            </a>
        @endif
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

        {{-- Productos --}}
        @php
            $productCount = $store?->products()->where('active', true)->count() ?? 0;
            $productLimit = $sub->hasFullAccess() ? null : $sub->plan?->max_products;
            $productPct   = $productLimit ? min(100, ($productCount / $productLimit) * 100) : 0;
            $productBar   = $productLimit
                ? ($productPct >= 90 ? 'bg-red-500' : ($productPct >= 70 ? 'bg-amber-400' : 'bg-blue-500'))
                : 'bg-blue-400';
        @endphp
        <div>
            <div class="flex justify-between text-xs text-slate-500 mb-1.5">
                <span class="font-medium text-slate-700">
                    <i class="fa-solid fa-box mr-1 text-blue-500"></i>Productos
                </span>
                <span>
                    {{ number_format($productCount) }}
                    @if($productLimit) / {{ number_format($productLimit) }}
                    @else &nbsp;·&nbsp; <span class="text-emerald-600 font-medium">Ilimitados</span>
                    @endif
                </span>
            </div>
            <div class="w-full bg-slate-100 rounded-full h-2">
                <div class="{{ $productBar }} h-2 rounded-full transition-all"
                     style="width: {{ $productLimit ? $productPct : 30 }}%; {{ !$productLimit ? 'opacity:.4' : '' }}">
                </div>
            </div>
        </div>

        {{-- Sucursales --}}
        @php
            $branchCount = $store?->branches()->where('active', true)->count() ?? 0;
            $branchLimit = $sub->hasFullAccess() ? null : $sub->plan?->max_branches;
            $branchPct   = $branchLimit ? min(100, ($branchCount / $branchLimit) * 100) : 0;
            $branchBar   = $branchLimit
                ? ($branchPct >= 90 ? 'bg-red-500' : ($branchPct >= 70 ? 'bg-amber-400' : 'bg-emerald-500'))
                : 'bg-emerald-400';
        @endphp
        <div>
            <div class="flex justify-between text-xs text-slate-500 mb-1.5">
                <span class="font-medium text-slate-700">
                    <i class="fa-solid fa-store mr-1 text-emerald-500"></i>Sucursales
                </span>
                <span>
                    {{ number_format($branchCount) }}
                    @if($branchLimit) / {{ number_format($branchLimit) }}
                    @else &nbsp;·&nbsp; <span class="text-emerald-600 font-medium">Ilimitadas</span>
                    @endif
                </span>
            </div>
            <div class="w-full bg-slate-100 rounded-full h-2">
                <div class="{{ $branchBar }} h-2 rounded-full transition-all"
                     style="width: {{ $branchLimit ? $branchPct : 30 }}%; {{ !$branchLimit ? 'opacity:.4' : '' }}">
                </div>
            </div>
        </div>

    </div>
</div>
@endif

{{-- ══ FILA 2: Sucursales y QR ══ --}}
<div class="bg-white rounded-xl border border-slate-200 p-5">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2">
            <span class="w-8 h-8 bg-emerald-50 rounded-lg flex items-center justify-center">
                <i class="fa-solid fa-qrcode text-emerald-600 text-sm"></i>
            </span>
            <h3 class="font-semibold text-slate-800">Sucursales y códigos QR</h3>
        </div>
        <a href="{{ route('dashboard.branches.index') }}"
           class="text-xs text-slate-400 hover:text-blue-600 transition">
            Administrar <i class="fa-solid fa-arrow-right ml-1"></i>
        </a>
    </div>

    @if($branches->isEmpty())
        <div class="text-center py-8 border border-dashed border-slate-200 rounded-xl">
            <i class="fa-solid fa-store text-3xl text-slate-300 mb-3 block"></i>
            <p class="text-sm font-medium text-slate-500">Todavía no tenés sucursales.</p>
            <p class="text-xs text-slate-400 mt-1 mb-4">Creá tu primera sucursal para generar su código QR.</p>
            <a href="{{ route('dashboard.branches.create') }}"
               class="inline-flex items-center gap-2 bg-blue-600 text-white text-sm px-4 py-2 rounded-lg font-medium hover:bg-blue-700 transition">
                <i class="fa-solid fa-plus"></i>
                Nueva sucursal
            </a>
        </div>
    @else
        <div class="divide-y divide-slate-100">
            @foreach($branches as $branch)
            <div class="flex items-center justify-between py-3 first:pt-0 last:pb-0">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 bg-slate-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid fa-store text-slate-500 text-sm"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-slate-800 truncate">{{ $branch->name }}</p>
                        @if($branch->address)
                            <p class="text-xs text-slate-400 truncate">{{ $branch->address }}</p>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0 ml-3">
                    <a href="{{ route('dashboard.branches.qr.configure', $branch) }}"
                       class="inline-flex items-center gap-1.5 bg-emerald-600 text-white text-xs px-3 py-1.5 rounded-lg font-medium hover:bg-emerald-700 transition">
                        <i class="fa-solid fa-print"></i>
                        <span class="hidden sm:inline">Imprimir QR</span>
                        <span class="sm:hidden">QR</span>
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>

@endsection
