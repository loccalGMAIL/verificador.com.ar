@extends('layouts.admin')

@section('title', 'Comercios')
@section('page-title', 'Comercios')

@section('content')

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
    <p class="text-sm text-slate-500">Total: {{ $stores->total() }} comercios</p>
</div>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold text-slate-600">Comercio</th>
                    <th class="text-left px-4 py-3 font-semibold text-slate-600 hidden md:table-cell">Plan</th>
                    <th class="text-left px-4 py-3 font-semibold text-slate-600 hidden md:table-cell">Subscripción</th>
                    <th class="text-center px-4 py-3 font-semibold text-slate-600 hidden lg:table-cell">Productos</th>
                    <th class="text-center px-4 py-3 font-semibold text-slate-600 hidden lg:table-cell">Sucursales</th>
                    <th class="text-left px-4 py-3 font-semibold text-slate-600">Estado</th>
                    <th class="text-right px-4 py-3 font-semibold text-slate-600">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($stores as $store)
                <tr class="hover:bg-slate-50 transition">
                    <td class="px-4 py-3">
                        <a href="{{ route('admin.stores.show', $store) }}"
                           class="font-medium text-blue-600 hover:underline">
                            {{ $store->name }}
                        </a>
                        @if($store->address)
                            <div class="text-xs text-slate-400 mt-0.5">{{ $store->address }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3 hidden md:table-cell text-slate-600">
                        {{ $store->subscription?->plan?->name ?? '—' }}
                    </td>
                    <td class="px-4 py-3 hidden md:table-cell">
                        @php $sub = $store->subscription; @endphp
                        @if($sub)
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
                                <span class="text-xs text-slate-400">{{ $sub->status }}</span>
                            @endif
                        @else
                            <span class="text-xs text-slate-400">Sin sub</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center hidden lg:table-cell text-slate-600">
                        {{ number_format($store->products_count) }}
                    </td>
                    <td class="px-4 py-3 text-center hidden lg:table-cell text-slate-600">
                        {{ $store->branches_count }}
                    </td>
                    <td class="px-4 py-3">
                        @if($store->status === 'active')
                            <span class="inline-flex items-center gap-1 text-xs font-medium bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">
                                <i class="fa-solid fa-circle text-[8px]"></i> Activo
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 text-xs font-medium bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full">
                                <i class="fa-solid fa-circle text-[8px]"></i> Suspendido
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.stores.show', $store) }}"
                               class="text-xs text-blue-600 hover:underline font-medium">Ver</a>
                            @if($store->status === 'active')
                                <form method="POST" action="{{ route('admin.stores.suspend', $store) }}"
                                      onsubmit="return confirm('¿Suspender este comercio?')">
                                    @csrf
                                    <button type="submit"
                                            class="text-xs text-red-600 hover:underline font-medium">Suspender</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.stores.reactivate', $store) }}">
                                    @csrf
                                    <button type="submit"
                                            class="text-xs text-emerald-600 hover:underline font-medium">Reactivar</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-10 text-center text-slate-400">
                        No hay comercios registrados.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($stores->hasPages())
<div class="mt-4">{{ $stores->links() }}</div>
@endif

@endsection
