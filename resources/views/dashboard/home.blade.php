@extends('layouts.app')

@section('title', 'Inicio')
@section('page-title', 'Inicio')

@section('content')
@php
    $store = auth()->user()->store;
    $sub   = $store?->subscription;
@endphp

{{-- Banner trial --}}
@if($sub && $sub->isOnTrial())
<div class="mb-6 bg-amber-50 border border-amber-200 rounded-xl px-5 py-4 flex items-start gap-3">
    <i class="fa-solid fa-clock-rotate-left text-amber-500 mt-0.5"></i>
    <div>
        <p class="font-semibold text-amber-800 text-sm">
            Estás en el período de prueba gratis
        </p>
        <p class="text-amber-700 text-sm mt-0.5">
            Te quedan <strong>{{ $sub->trialDaysRemaining() }} día(s)</strong>.
            <a href="{{ route('dashboard.subscription') }}" class="underline font-medium">
                Activá tu subscripción
            </a>
            para continuar usando el servicio.
        </p>
    </div>
</div>
@endif

{{-- Stats cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm font-medium text-slate-500">Productos</span>
            <span class="w-9 h-9 bg-blue-50 rounded-lg flex items-center justify-center">
                <i class="fa-solid fa-box text-blue-600 text-sm"></i>
            </span>
        </div>
        <p class="text-3xl font-bold text-slate-900">{{ $store?->products()->where('active', true)->count() ?? 0 }}</p>
        <p class="text-xs text-slate-400 mt-1">
            @if($sub && $sub->plan)
                Límite: {{ $sub->plan->maxProductsLabel() }}
            @endif
        </p>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm font-medium text-slate-500">Sucursales</span>
            <span class="w-9 h-9 bg-emerald-50 rounded-lg flex items-center justify-center">
                <i class="fa-solid fa-store text-emerald-600 text-sm"></i>
            </span>
        </div>
        <p class="text-3xl font-bold text-slate-900">{{ $store?->branches()->where('active', true)->count() ?? 0 }}</p>
        <p class="text-xs text-slate-400 mt-1">sucursales activas</p>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm font-medium text-slate-500">Plan</span>
            <span class="w-9 h-9 bg-violet-50 rounded-lg flex items-center justify-center">
                <i class="fa-solid fa-credit-card text-violet-600 text-sm"></i>
            </span>
        </div>
        <p class="text-3xl font-bold text-slate-900">{{ $sub?->plan?->name ?? 'Sin plan' }}</p>
        <p class="text-xs mt-1 {{ $sub?->isOnTrial() ? 'text-amber-500' : 'text-slate-400' }}">
            @if($sub?->isOnTrial())
                Trial activo
            @elseif($sub?->isActive())
                Activa
            @else
                {{ ucfirst($sub?->status ?? 'sin subscripción') }}
            @endif
        </p>
    </div>
</div>

{{-- Acciones rápidas --}}
<div class="bg-white rounded-xl border border-slate-200 p-5">
    <h3 class="font-semibold text-slate-800 mb-4">Acciones rápidas</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <a href="{{ route('dashboard.products.index') }}"
           class="flex items-center gap-3 p-3 rounded-lg border border-slate-200 hover:border-blue-300 hover:bg-blue-50 transition">
            <i class="fa-solid fa-box text-blue-600"></i>
            <div>
                <p class="text-sm font-medium text-slate-800">Ver productos</p>
                <p class="text-xs text-slate-400">Gestionar catálogo</p>
            </div>
        </a>
        <a href="{{ route('dashboard.branches.index') }}"
           class="flex items-center gap-3 p-3 rounded-lg border border-slate-200 hover:border-emerald-300 hover:bg-emerald-50 transition">
            <i class="fa-solid fa-store text-emerald-600"></i>
            <div>
                <p class="text-sm font-medium text-slate-800">Ver sucursales</p>
                <p class="text-xs text-slate-400">Administrar sucursales y QRs</p>
            </div>
        </a>
    </div>
</div>
@endsection
