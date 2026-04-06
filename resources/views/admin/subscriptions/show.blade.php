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

{{-- Flash --}}
@if(session('success'))
<div class="mb-5 bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 flex items-center gap-2 text-sm text-emerald-800">
    <i class="fa-solid fa-circle-check text-emerald-500"></i>
    {{ session('success') }}
</div>
@endif

@if($errors->any())
<div class="mb-5 bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-800">
    <ul class="list-disc list-inside space-y-0.5">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

{{-- Registrar pago manual --}}
<div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 mb-5">
    <h2 class="text-sm font-semibold text-slate-700 mb-4">
        <i class="fa-solid fa-plus-circle text-indigo-500 mr-1.5"></i>
        Registrar pago manual
    </h2>
    <form method="POST" action="{{ route('admin.subscriptions.payments.store', $subscription) }}">
        @csrf
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Monto</label>
                <input type="number" name="amount" step="0.01" min="0.01"
                       value="{{ old('amount') }}"
                       placeholder="Ej: 5000.00"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 @error('amount') border-red-400 @enderror"
                       required>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Moneda</label>
                <select name="currency"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    <option value="ARS" @selected(old('currency', 'ARS') === 'ARS')>ARS</option>
                    <option value="USD" @selected(old('currency') === 'USD')>USD</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Fecha de pago</label>
                <input type="date" name="paid_at"
                       value="{{ old('paid_at', now()->format('Y-m-d')) }}"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 @error('paid_at') border-red-400 @enderror"
                       required>
            </div>
            <div class="flex items-end">
                <button type="submit"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
                    <i class="fa-solid fa-check mr-1"></i> Registrar pago
                </button>
            </div>
        </div>
        <div class="mt-3">
            <label class="block text-xs font-medium text-slate-600 mb-1">Notas <span class="text-slate-400 font-normal">(opcional)</span></label>
            <input type="text" name="notes" maxlength="500"
                   value="{{ old('notes') }}"
                   placeholder="Ej: Transferencia bancaria CBU 000..."
                   class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
        </div>
    </form>
</div>

{{-- Historial de pagos --}}
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="p-5 border-b border-slate-100 flex items-center justify-between">
        <h2 class="text-sm font-semibold text-slate-700">Historial de pagos</h2>
        @if($subscription->payments->isNotEmpty())
        @php
            $totalPaid = $subscription->payments->where('status', 'processed')->sum('amount');
            $countPaid = $subscription->payments->where('status', 'processed')->count();
        @endphp
        <span class="text-xs text-slate-400">
            {{ $countPaid }} acreditado(s) · ARS {{ number_format($totalPaid, 2, ',', '.') }}
        </span>
        @endif
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold text-slate-600">Fecha</th>
                    <th class="text-left px-4 py-3 font-semibold text-slate-600">Monto</th>
                    <th class="text-left px-4 py-3 font-semibold text-slate-600">Estado</th>
                    <th class="text-left px-4 py-3 font-semibold text-slate-600">Origen</th>
                    <th class="text-left px-4 py-3 font-semibold text-slate-600 hidden lg:table-cell">Notas / ID MP</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($subscription->payments as $payment)
                <tr class="hover:bg-slate-50 transition">
                    <td class="px-4 py-3 text-slate-600 whitespace-nowrap">
                        {{ $payment->paid_at?->format('d/m/Y') ?? '—' }}
                    </td>
                    <td class="px-4 py-3 font-semibold text-slate-800 whitespace-nowrap">
                        {{ $payment->currency }} {{ number_format($payment->amount, 2, ',', '.') }}
                    </td>
                    <td class="px-4 py-3">
                        @php
                            $payBadge = match($payment->status) {
                                'processed' => ['bg-emerald-100 text-emerald-700', 'Acreditado'],
                                'recycling' => ['bg-amber-100 text-amber-700', 'Reintentando'],
                                'cancelled' => ['bg-red-100 text-red-700', 'Cancelado'],
                                default     => ['bg-slate-100 text-slate-500', $payment->status],
                            };
                        @endphp
                        <span class="inline-flex items-center text-xs font-medium px-2 py-0.5 rounded-full {{ $payBadge[0] }}">
                            {{ $payBadge[1] }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        @if($payment->isManual())
                            <span class="inline-flex items-center text-xs font-medium px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700">
                                <i class="fa-solid fa-user-shield mr-1 text-[10px]"></i> Manual
                            </span>
                        @else
                            <span class="inline-flex items-center text-xs font-medium px-2 py-0.5 rounded-full bg-blue-100 text-blue-700">
                                <i class="fa-solid fa-credit-card mr-1 text-[10px]"></i> MercadoPago
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3 hidden lg:table-cell text-xs text-slate-400 max-w-xs truncate">
                        @if($payment->notes)
                            <span class="text-slate-600">{{ $payment->notes }}</span>
                        @elseif($payment->mp_payment_id)
                            <span class="font-mono">{{ $payment->mp_payment_id }}</span>
                        @else
                            —
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-10 text-center text-slate-400">
                        Sin pagos registrados.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
