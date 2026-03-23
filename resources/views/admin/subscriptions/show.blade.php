@extends('layouts.admin')

@section('title', 'Suscripción — ' . ($subscription->store?->name ?? 'Sin comercio'))
@section('page-title', 'Detalle de suscripción')

@section('content')

{{-- Breadcrumb --}}
<div class="flex items-center gap-2 text-sm text-slate-500 mb-5">
    <a href="{{ route('admin.subscriptions.index') }}" class="hover:text-blue-600 transition">Suscripciones</a>
    <i class="fa-solid fa-chevron-right text-xs"></i>
    <span class="text-slate-800 font-medium">{{ $subscription->store?->name ?? 'Sin comercio' }}</span>
</div>

{{-- Información de la suscripción --}}
<div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 mb-5">
    <h2 class="text-sm font-semibold text-slate-700 mb-4">Información de la suscripción</h2>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
        <div>
            <div class="text-xs text-slate-400 uppercase tracking-wide mb-1">Comercio</div>
            @if($subscription->store)
                <a href="{{ route('admin.stores.show', $subscription->store) }}"
                   class="text-sm font-medium text-blue-600 hover:underline">
                    {{ $subscription->store->name }}
                </a>
            @else
                <span class="text-sm text-slate-400">—</span>
            @endif
        </div>
        <div>
            <div class="text-xs text-slate-400 uppercase tracking-wide mb-1">Plan</div>
            <span class="text-sm font-medium text-slate-700">{{ $subscription->plan?->name ?? '—' }}</span>
        </div>
        <div>
            <div class="text-xs text-slate-400 uppercase tracking-wide mb-1">Estado</div>
            @php
                $badgeMap = [
                    'trial'     => 'bg-amber-100 text-amber-700',
                    'active'    => 'bg-emerald-100 text-emerald-700',
                    'suspended' => 'bg-red-100 text-red-700',
                    'cancelled' => 'bg-slate-100 text-slate-500',
                ];
                $badgeClass = $badgeMap[$subscription->status] ?? 'bg-slate-100 text-slate-500';
            @endphp
            <span class="inline-flex items-center text-xs font-medium px-2 py-0.5 rounded-full {{ $badgeClass }}">
                {{ $subscription->statusLabel() }}
            </span>
        </div>
        <div>
            <div class="text-xs text-slate-400 uppercase tracking-wide mb-1">Activa desde</div>
            <span class="text-sm text-slate-600">{{ $subscription->starts_at?->format('d/m/Y') ?? '—' }}</span>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <div>
            <div class="text-xs text-slate-400 uppercase tracking-wide mb-1">Fin del trial</div>
            <span class="text-sm text-slate-600">{{ $subscription->trial_ends_at?->format('d/m/Y H:i') ?? '—' }}</span>
        </div>
        <div>
            <div class="text-xs text-slate-400 uppercase tracking-wide mb-1">Creada</div>
            <span class="text-sm text-slate-600">{{ $subscription->created_at->format('d/m/Y H:i') }}</span>
        </div>
    </div>

    @if($subscription->mp_subscription_id)
    <div class="border-t border-slate-100 pt-4 mt-2">
        <div class="text-xs text-slate-400 uppercase tracking-wide mb-3">MercadoPago</div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <div class="text-xs text-slate-400 mb-1">Subscription ID</div>
                <div class="flex items-center gap-2">
                    <code class="text-xs font-mono text-slate-600 bg-slate-50 border border-slate-200 rounded px-2 py-1 break-all">
                        {{ $subscription->mp_subscription_id }}
                    </code>
                </div>
            </div>
            <div>
                <div class="text-xs text-slate-400 mb-1">Payer ID</div>
                <span class="text-sm text-slate-600 font-mono">{{ $subscription->mp_payer_id ?? '—' }}</span>
            </div>
            <div>
                <div class="text-xs text-slate-400 mb-1">Payer Email</div>
                <span class="text-sm text-slate-600">{{ $subscription->mp_payer_email ?? '—' }}</span>
            </div>
        </div>
    </div>
    @endif
</div>

{{-- Historial de pagos --}}
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="p-5 border-b border-slate-100">
        <h2 class="text-sm font-semibold text-slate-700">Historial de pagos</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold text-slate-600">Fecha</th>
                    <th class="text-left px-4 py-3 font-semibold text-slate-600">Monto</th>
                    <th class="text-left px-4 py-3 font-semibold text-slate-600">Estado</th>
                    <th class="text-left px-4 py-3 font-semibold text-slate-600 hidden md:table-cell">ID MP</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($subscription->payments as $payment)
                <tr class="hover:bg-slate-50 transition">
                    <td class="px-4 py-3 text-slate-600">
                        {{ $payment->paid_at?->format('d/m/Y H:i') ?? '—' }}
                    </td>
                    <td class="px-4 py-3 font-medium text-slate-700">
                        {{ $payment->currency }} {{ number_format($payment->amount, 2, ',', '.') }}
                    </td>
                    <td class="px-4 py-3">
                        @php
                            $payBadge = match($payment->status) {
                                'processed' => 'bg-emerald-100 text-emerald-700',
                                'recycling' => 'bg-amber-100 text-amber-700',
                                'cancelled' => 'bg-red-100 text-red-700',
                                default     => 'bg-slate-100 text-slate-500',
                            };
                        @endphp
                        <span class="inline-flex items-center text-xs font-medium px-2 py-0.5 rounded-full {{ $payBadge }}">
                            {{ $payment->status }}
                        </span>
                    </td>
                    <td class="px-4 py-3 hidden md:table-cell text-xs font-mono text-slate-400">
                        {{ $payment->mp_payment_id }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-10 text-center text-slate-400">
                        Sin pagos registrados.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
