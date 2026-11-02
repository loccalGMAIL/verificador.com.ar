@extends('layouts.app')

@section('title', 'Configuración')
@section('page-title', 'Configuración')

@section('content')

@php $settingsActiveTab = 'api'; $apiActiveTab = 'basic'; @endphp
@include('dashboard.settings._tabs')
@include('dashboard.settings.api._tabs')

@if(session('success'))
<div class="mb-5 bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 text-sm text-emerald-700">
    <i class="fa-solid fa-circle-check mr-2"></i>{{ session('success') }}
</div>
@endif

<div class="max-w-3xl space-y-6">

    {{-- ── Estado del plan ──────────────────────────────────────── --}}
    @unless($hasApi)
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-5 flex items-start gap-4">
        <i class="fa-solid fa-lock text-amber-500 mt-0.5"></i>
        <div class="flex-1">
            <h3 class="font-semibold text-amber-900">Tu plan no incluye integraciones por API</h3>
            <p class="text-sm text-amber-800 mt-1">
                Actualizá tu plan para conectar tu sistema de gestión y mantener precios y productos sincronizados automáticamente.
            </p>
        </div>
        <a href="{{ route('dashboard.subscription') }}"
           class="bg-amber-500 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-amber-600 transition whitespace-nowrap">
            Ver planes
        </a>
    </div>
    @endunless

    {{-- ── Integraciones ────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-slate-200">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="font-semibold text-slate-800">Integraciones</h3>
            <p class="text-xs text-slate-400 mt-0.5">Conectá tu sistema de gestión para traer productos y precios automáticamente.</p>
        </div>

        {{-- Odoo --}}
        <div class="px-6 py-5 flex flex-col sm:flex-row sm:items-start gap-4">
            <div class="w-10 h-10 rounded-lg bg-violet-100 text-violet-700 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-cube"></i>
            </div>

            <div class="flex-1 min-w-0 space-y-2">
                <div class="flex items-center gap-2 flex-wrap">
                    <h4 class="font-semibold text-slate-800">Odoo</h4>
                    @if(! $odooConnection)
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-500">No conectado</span>
                    @elseif($odooConnection->lastSyncFailed())
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Error de sincronización</span>
                    @elseif(! $odooConnection->enabled)
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Automática pausada</span>
                    @else
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Conectado</span>
                    @endif
                </div>

                @if($odooConnection)
                    <p class="text-xs text-slate-500 break-all">{{ $odooConnection->url }}</p>
                    <p class="text-xs text-slate-500">
                        Última sincronización: {{ $odooConnection->last_synced_at?->diffForHumans() ?? 'pendiente' }}
                        · {{ number_format($odooProductCount, 0, ',', '.') }} productos desde Odoo
                    </p>
                    @if($odooConnection->lastSyncFailed())
                        <p class="text-xs text-red-600">{{ $odooConnection->last_error }}</p>
                    @endif

                    @if($hasApi)
                    <form method="POST" action="{{ route('dashboard.settings.odoo.auto-sync') }}" class="pt-1">
                        @csrf @method('PUT')
                        <button type="submit" class="flex items-center gap-3 text-sm text-slate-700"
                                title="{{ $odooConnection->enabled ? 'Pausar' : 'Reanudar' }} sincronización automática">
                            <span class="relative inline-block w-11 h-6 rounded-full transition {{ $odooConnection->enabled ? 'bg-blue-600' : 'bg-slate-200' }}">
                                <span class="absolute top-0.5 w-5 h-5 bg-white rounded-full shadow transition {{ $odooConnection->enabled ? 'left-[22px]' : 'left-0.5' }}"></span>
                            </span>
                            Sincronización automática cada 30 minutos
                        </button>
                    </form>
                    @endif
                @else
                    <p class="text-xs text-slate-500">Traé nombre, código de barras y precio de tus productos de Odoo 19 o superior.</p>
                @endif
            </div>

            @if($hasApi)
            <a href="{{ route('dashboard.settings.odoo') }}"
               class="self-start bg-slate-100 text-slate-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-slate-200 transition whitespace-nowrap">
                {{ $odooConnection ? 'Configurar' : 'Conectar' }}
            </a>
            @endif
        </div>

        {{-- Próximamente --}}
        <div class="px-6 py-4 border-t border-slate-100 text-xs text-slate-400 flex items-center gap-2">
            <i class="fa-solid fa-plus"></i>
            Próximamente más integraciones. ¿Usás otro sistema? Contanos cuál.
        </div>
    </div>
</div>

@endsection
