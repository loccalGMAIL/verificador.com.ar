@extends('layouts.admin')

@section('title', 'Subscripciones')
@section('page-title', 'Subscripciones')

@section('content')

<div class="mb-5">
    <p class="text-sm text-slate-500">Total: {{ $subscriptions->total() }} subscripciones</p>
</div>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold text-slate-600">Comercio</th>
                    <th class="text-left px-4 py-3 font-semibold text-slate-600">Plan</th>
                    <th class="text-left px-4 py-3 font-semibold text-slate-600">Estado</th>
                    <th class="text-left px-4 py-3 font-semibold text-slate-600 hidden md:table-cell">Trial hasta</th>
                    <th class="text-left px-4 py-3 font-semibold text-slate-600 hidden lg:table-cell">Desde</th>
                    <th class="text-right px-4 py-3 font-semibold text-slate-600">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($subscriptions as $sub)
                <tr class="hover:bg-slate-50 transition">
                    <td class="px-4 py-3">
                        @if($sub->store)
                            <a href="{{ route('admin.stores.show', $sub->store) }}"
                               class="font-medium text-blue-600 hover:underline">
                                {{ $sub->store->name }}
                            </a>
                        @else
                            <span class="text-slate-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-slate-600">
                        {{ $sub->plan?->name ?? '—' }}
                    </td>
                    <td class="px-4 py-3">
                        @if($sub->status === 'trial')
                            <span class="inline-flex items-center gap-1 text-xs font-medium bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full">
                                <i class="fa-solid fa-hourglass-half text-[10px]"></i> Trial
                            </span>
                        @elseif($sub->status === 'active')
                            <span class="inline-flex items-center gap-1 text-xs font-medium bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">
                                <i class="fa-solid fa-circle-check text-[10px]"></i> Activa
                            </span>
                        @elseif($sub->status === 'suspended')
                            <span class="inline-flex items-center gap-1 text-xs font-medium bg-red-100 text-red-700 px-2 py-0.5 rounded-full">
                                <i class="fa-solid fa-ban text-[10px]"></i> Suspendida
                            </span>
                        @else
                            <span class="text-xs text-slate-500">{{ $sub->status }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 hidden md:table-cell text-slate-500 text-xs">
                        {{ $sub->trial_ends_at?->format('d/m/Y') ?? '—' }}
                    </td>
                    <td class="px-4 py-3 hidden lg:table-cell text-slate-500 text-xs">
                        {{ $sub->created_at->format('d/m/Y') }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-3">
                            {{-- Change plan inline --}}
                            <form method="POST" action="{{ route('admin.subscriptions.change-plan', $sub) }}"
                                  class="flex items-center gap-1">
                                @csrf
                                <select name="plan_id"
                                        class="border border-slate-300 rounded-lg px-2 py-1 text-xs focus:ring-1 focus:ring-blue-500 focus:outline-none">
                                    @foreach(\App\Models\Plan::where('active', true)->orderBy('sort_order')->get() as $plan)
                                        <option value="{{ $plan->id }}" {{ $sub->plan_id == $plan->id ? 'selected' : '' }}>
                                            {{ $plan->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit"
                                        class="text-xs text-blue-600 hover:underline font-medium">Cambiar</button>
                            </form>

                            {{-- Reset trial --}}
                            <form method="POST" action="{{ route('admin.subscriptions.reset-trial', $sub) }}"
                                  onsubmit="return confirm('¿Reiniciar trial por 7 días?')">
                                @csrf
                                <button type="submit" class="text-xs text-amber-600 hover:underline font-medium">
                                    <i class="fa-solid fa-rotate-left"></i> Trial
                                </button>
                            </form>

                            {{-- Suspend / reactivate --}}
                            @if($sub->status !== 'suspended')
                                <form method="POST" action="{{ route('admin.subscriptions.suspend', $sub) }}"
                                      onsubmit="return confirm('¿Suspender?')">
                                    @csrf
                                    <button type="submit" class="text-xs text-red-600 hover:underline font-medium">Suspender</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.subscriptions.reactivate', $sub) }}">
                                    @csrf
                                    <button type="submit" class="text-xs text-emerald-600 hover:underline font-medium">Reactivar</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-10 text-center text-slate-400">
                        No hay subscripciones registradas.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($subscriptions->hasPages())
<div class="mt-4">{{ $subscriptions->links() }}</div>
@endif

@endsection
