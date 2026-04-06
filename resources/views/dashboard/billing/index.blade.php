@extends('layouts.app')

@section('title', 'Estado de Cuenta')
@section('page-title', 'Estado de Cuenta')

@section('content')

@php
    $payments  = $sub?->payments?->sortByDesc('paid_at') ?? collect();
    $totalPaid = $payments->where('status', 'processed')->sum('amount');
    $lastPayment = $payments->where('status', 'processed')->first();
@endphp

{{-- ══ RESUMEN ══ --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

    {{-- Plan actual --}}
    <div class="bg-white rounded-xl border border-slate-200 px-4 py-4 flex items-center gap-3">
        <span class="w-10 h-10 bg-violet-50 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa-solid fa-credit-card text-violet-600"></i>
        </span>
        <div class="min-w-0">
            <p class="text-xs text-slate-400 mb-0.5">Plan actual</p>
            <p class="font-bold text-slate-800 text-sm truncate">{{ $sub?->plan?->name ?? '—' }}</p>
            @if($sub?->plan?->price_ars > 0)
                <p class="text-xs text-slate-400">ARS {{ number_format($sub->plan->price_ars, 2, ',', '.') }}/mes</p>
            @elseif($sub?->plan)
                <p class="text-xs text-slate-400">Gratuito</p>
            @endif
        </div>
    </div>

    {{-- Estado --}}
    <div class="bg-white rounded-xl border border-slate-200 px-4 py-4 flex items-center gap-3">
        @php
            $statusIcon  = $sub?->isOnTrial() ? 'fa-clock text-amber-500' : ($sub?->isActive() ? 'fa-circle-check text-emerald-500' : 'fa-circle-xmark text-red-500');
            $statusBg    = $sub?->isOnTrial() ? 'bg-amber-50' : ($sub?->isActive() ? 'bg-emerald-50' : 'bg-red-50');
        @endphp
        <span class="w-10 h-10 {{ $statusBg }} rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa-solid {{ $statusIcon }}"></i>
        </span>
        <div>
            <p class="text-xs text-slate-400 mb-0.5">Estado</p>
            <p class="font-bold text-slate-800 text-sm">{{ $sub?->statusLabel() ?? '—' }}</p>
            @if($sub?->isOnTrial())
                <p class="text-xs text-amber-500">{{ $sub->trialDaysRemaining() }} día(s) restantes</p>
            @endif
        </div>
    </div>

    {{-- Próximo vencimiento --}}
    <div class="bg-white rounded-xl border border-slate-200 px-4 py-4 flex items-center gap-3">
        @php
            $dueSoon = $nextDue && $nextDue->diffInDays(now(), false) >= -7;
        @endphp
        <span class="w-10 h-10 {{ $dueSoon ? 'bg-amber-50' : 'bg-blue-50' }} rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa-solid fa-calendar-day {{ $dueSoon ? 'text-amber-500' : 'text-blue-600' }}"></i>
        </span>
        <div>
            <p class="text-xs text-slate-400 mb-0.5">Próximo vencimiento</p>
            @if($nextDue)
                <p class="font-bold text-slate-800 text-sm">{{ $nextDue->format('d/m/Y') }}</p>
                @if($dueSoon)
                    <p class="text-xs text-amber-500">Próximamente</p>
                @else
                    <p class="text-xs text-slate-400">En {{ (int) now()->diffInDays($nextDue) }} día(s)</p>
                @endif
            @else
                <p class="font-bold text-slate-400 text-sm">—</p>
            @endif
        </div>
    </div>

    {{-- Total abonado --}}
    <div class="bg-white rounded-xl border border-slate-200 px-4 py-4 flex items-center gap-3">
        <span class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa-solid fa-money-bill-wave text-emerald-600"></i>
        </span>
        <div>
            <p class="text-xs text-slate-400 mb-0.5">Total abonado</p>
            <p class="font-bold text-slate-800 text-sm">ARS {{ number_format($totalPaid, 2, ',', '.') }}</p>
            @if($lastPayment)
                <p class="text-xs text-slate-400">Último: {{ $lastPayment->paid_at?->format('d/m/Y') }}</p>
            @endif
        </div>
    </div>

</div>

{{-- ══ ALERTA VENCIMIENTO PRÓXIMO ══ --}}
@if($nextDue && $nextDue->isPast())
<div class="mb-5 bg-red-50 border border-red-200 rounded-xl px-4 py-3 flex items-center gap-3 text-sm">
    <i class="fa-solid fa-circle-exclamation text-red-500 flex-shrink-0"></i>
    <div>
        <span class="font-semibold text-red-800">Tu suscripción venció el {{ $nextDue->format('d/m/Y') }}.</span>
        <span class="text-red-700 ml-1">Contactate con nosotros para regularizar tu cuenta.</span>
    </div>
</div>
@elseif($nextDue && $nextDue->diffInDays(now(), false) >= -7)
<div class="mb-5 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 flex items-center gap-3 text-sm">
    <i class="fa-solid fa-clock text-amber-500 flex-shrink-0"></i>
    <span class="text-amber-800">
        Tu próximo vencimiento es el <strong>{{ $nextDue->format('d/m/Y') }}</strong>.
        Asegurate de tener todo al día.
    </span>
</div>
@endif

{{-- ══ HISTORIAL DE PAGOS ══ --}}
<div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between gap-3">
        <h2 class="text-sm font-semibold text-slate-700">Historial de pagos</h2>
        @if($payments->isNotEmpty())
            <span class="text-xs text-slate-400">
                {{ $payments->where('status', 'processed')->count() }} acreditado(s)
            </span>
        @endif
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr class="text-left">
                    <th class="px-4 py-3 font-semibold text-slate-500">Fecha</th>
                    <th class="px-4 py-3 font-semibold text-slate-500">Monto</th>
                    <th class="px-4 py-3 font-semibold text-slate-500">Estado</th>
                    <th class="px-4 py-3 font-semibold text-slate-500 hidden sm:table-cell">Detalle</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($payments as $payment)
                <tr class="hover:bg-slate-50 transition">
                    <td class="px-4 py-3 text-slate-600 whitespace-nowrap">
                        {{ $payment->paid_at?->format('d/m/Y') ?? '—' }}
                    </td>
                    <td class="px-4 py-3 font-semibold text-slate-800 whitespace-nowrap">
                        {{ $payment->currency }} {{ number_format($payment->amount, 2, ',', '.') }}
                    </td>
                    <td class="px-4 py-3">
                        @php
                            $badge = match($payment->status) {
                                'processed' => ['bg-emerald-100 text-emerald-700', 'Acreditado'],
                                'recycling' => ['bg-amber-100 text-amber-700', 'Reintentando'],
                                'cancelled' => ['bg-red-100 text-red-700', 'Cancelado'],
                                default     => ['bg-slate-100 text-slate-500', $payment->status],
                            };
                        @endphp
                        <span class="inline-flex items-center text-xs font-medium px-2 py-0.5 rounded-full {{ $badge[0] }}">
                            {{ $badge[1] }}
                        </span>
                    </td>
                    <td class="px-4 py-3 hidden sm:table-cell text-xs text-slate-400">
                        @if($payment->notes)
                            {{ $payment->notes }}
                        @elseif($payment->mp_payment_id)
                            MercadoPago
                        @else
                            —
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-10 text-center text-slate-400">
                        <i class="fa-regular fa-file-lines text-3xl block mb-2 opacity-30"></i>
                        <p class="text-sm">Todavía no hay pagos registrados.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
