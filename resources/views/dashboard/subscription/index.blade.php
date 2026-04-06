@extends('layouts.app')

@section('title', 'Suscripción')
@section('page-title', 'Suscripción')

@section('content')

{{-- ══ BANNER: retorno de MercadoPago ══ --}}
@if(session('mp_return_status') === 'success')
<div class="mb-6 bg-emerald-50 border border-emerald-200 rounded-xl px-5 py-4 flex items-start gap-3">
    <i class="fa-solid fa-circle-check text-emerald-500 mt-0.5 text-lg"></i>
    <div>
        <p class="font-semibold text-emerald-800 text-sm">¡Suscripción procesada!</p>
        <p class="text-emerald-700 text-sm mt-0.5">
            Tu suscripción fue autorizada. Si no ves el cambio de estado todavía, esperá unos segundos y recargá la página.
        </p>
    </div>
</div>
@elseif(session('mp_return_status') === 'pending')
<div class="mb-6 bg-amber-50 border border-amber-200 rounded-xl px-5 py-4 flex items-start gap-3">
    <i class="fa-solid fa-clock text-amber-500 mt-0.5 text-lg"></i>
    <div>
        <p class="font-semibold text-amber-800 text-sm">Pago pendiente</p>
        <p class="text-amber-700 text-sm mt-0.5">
            Tu pago está siendo procesado. Te avisaremos cuando se acredite. Tu acceso actual sigue vigente.
        </p>
    </div>
</div>
@elseif(session('mp_return_status') === 'failure')
<div class="mb-6 bg-red-50 border border-red-200 rounded-xl px-5 py-4 flex items-start gap-3">
    <i class="fa-solid fa-circle-xmark text-red-500 mt-0.5 text-lg"></i>
    <div>
        <p class="font-semibold text-red-800 text-sm">El pago no pudo completarse</p>
        <p class="text-red-700 text-sm mt-0.5">
            Hubo un problema con el pago. Podés intentarlo de nuevo eligiendo tu plan.
        </p>
    </div>
</div>
@endif

{{-- ══ BANNER: suscripción expirada ══ --}}
@if(session('subscription_expired') || ($sub && $sub->isExpired()))
<div class="mb-6 bg-red-50 border border-red-200 rounded-xl px-5 py-4 flex items-start gap-3">
    <i class="fa-solid fa-circle-exclamation text-red-500 mt-0.5 text-lg"></i>
    <div>
        <p class="font-semibold text-red-800 text-sm">Tu acceso ha expirado</p>
        <p class="text-red-700 text-sm mt-0.5">
            Tu período de prueba terminó. Elegí un plan para continuar usando el sistema.
        </p>
    </div>
</div>
@endif

{{-- ══ ESTADO ACTUAL ══ --}}
@if($sub)
<div class="bg-white rounded-xl border border-slate-200 p-5 mb-6">
    <h3 class="text-sm font-semibold text-slate-700 mb-4">Estado de tu suscripción</h3>
    <div class="flex flex-col sm:flex-row sm:items-center gap-4">

        {{-- Plan --}}
        <div class="flex items-center gap-3 flex-1">
            <span class="w-10 h-10 bg-violet-50 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-credit-card text-violet-600"></i>
            </span>
            <div>
                <p class="text-xs text-slate-500">Plan actual</p>
                <p class="font-bold text-slate-800">{{ $sub->plan?->name ?? '—' }}</p>
            </div>
        </div>

        {{-- Estado --}}
        <div class="flex items-center gap-3 flex-1">
            <span class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0
                {{ $sub->isOnTrial() ? 'bg-amber-50' : ($sub->isActive() ? 'bg-emerald-50' : 'bg-red-50') }}">
                <i class="fa-solid {{ $sub->isOnTrial() ? 'fa-clock text-amber-500' : ($sub->isActive() ? 'fa-circle-check text-emerald-600' : 'fa-circle-xmark text-red-500') }}"></i>
            </span>
            <div>
                <p class="text-xs text-slate-500">Estado</p>
                <p class="font-bold
                    {{ $sub->isOnTrial() ? 'text-amber-600' : ($sub->isActive() ? 'text-emerald-700' : 'text-red-600') }}">
                    {{ $sub->statusLabel() }}
                    @if($sub->isOnTrial())
                        · {{ $sub->trialDaysRemaining() }} día(s) restantes
                    @endif
                </p>
            </div>
        </div>

        {{-- Acceso --}}
        <div class="flex items-center gap-3 flex-1">
            <span class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-shield-halved text-blue-600"></i>
            </span>
            <div>
                <p class="text-xs text-slate-500">Nivel de acceso</p>
                <p class="font-bold text-slate-800">
                    @if($sub->hasFullAccess())
                        Acceso total <span class="text-xs font-normal text-slate-500">(trial Business)</span>
                    @elseif($sub->isActive())
                        Según plan {{ $sub->plan?->name }}
                    @else
                        Sin acceso
                    @endif
                </p>
            </div>
        </div>

    </div>

    {{-- Barra trial --}}
    @if($sub->isOnTrial())
    @php
        $daysTotal     = config('app.trial_days');
        $daysRemaining = $sub->trialDaysRemaining();
        $pct           = max(0, min(100, ($daysRemaining / $daysTotal) * 100));
        $barColor      = $daysRemaining <= 2 ? 'bg-red-500' : ($daysRemaining <= 4 ? 'bg-amber-400' : 'bg-emerald-500');
    @endphp
    <div class="mt-4 pt-4 border-t border-slate-100">
        <div class="flex justify-between text-xs text-slate-500 mb-1.5">
            <span>Trial</span>
            <span>{{ $daysRemaining }} / {{ $daysTotal }} días restantes</span>
        </div>
        <div class="w-full bg-slate-100 rounded-full h-2">
            <div class="{{ $barColor }} h-2 rounded-full transition-all" style="width: {{ $pct }}%"></div>
        </div>
    </div>
    @endif
</div>
@endif

{{-- ══ TABLA DE PLANES ══ --}}
<div class="mb-4">
    <h3 class="font-semibold text-slate-800">
        @if($sub?->isExpired())
            Elegí tu plan para continuar
        @elseif($sub?->isOnTrial())
            Elegí tu plan antes de que expire el trial
        @else
            Planes disponibles
        @endif
    </h3>
    <p class="text-sm text-slate-500 mt-0.5">Los precios están expresados en pesos argentinos por mes.</p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
    @foreach($plans as $plan)
    @php $isCurrent = $sub?->plan_id === $plan->id && $sub?->isActive(); @endphp
    <x-plan-card :plan="$plan" :featured="$plan->featured" variant="dashboard">
        @if($isCurrent)
            <div class="w-full text-center py-2 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-semibold">
                <i class="fa-solid fa-circle-check mr-1"></i> Plan actual
            </div>
        @else
            <form method="POST" action="{{ route('dashboard.subscription.subscribe', $plan) }}">
                @csrf
                <button type="submit"
                        class="w-full py-2.5 rounded-lg text-sm font-semibold transition
                               {{ $plan->featured
                                   ? 'bg-blue-600 text-white hover:bg-blue-700'
                                   : 'bg-slate-800 text-white hover:bg-slate-900' }}">
                    @if($sub?->isExpired())
                        Activar plan
                    @elseif($sub?->isActive() && $sub->plan && $plan->price_usd > $sub->plan->price_usd)
                        Mejorar a {{ $plan->name }}
                    @elseif($sub?->isActive() && $sub->plan && $plan->price_usd < $sub->plan->price_usd)
                        Cambiar a {{ $plan->name }}
                    @else
                        Elegir {{ $plan->name }}
                    @endif
                </button>
            </form>
        @endif
    </x-plan-card>
    @endforeach
</div>

{{-- Nota de pago --}}
<p class="text-xs text-slate-400 text-center">
    <i class="fa-solid fa-lock mr-1"></i>
    Los pagos se procesan de forma segura a través de MercadoPago.
</p>

{{-- Link a estado de cuenta --}}
@if($sub)
<p class="mt-6 text-center text-sm text-slate-500">
    <a href="{{ route('dashboard.billing') }}" class="text-blue-600 hover:underline font-medium">
        <i class="fa-solid fa-file-invoice-dollar mr-1"></i>
        Ver estado de cuenta e historial de pagos
    </a>
</p>
@endif

@endsection
