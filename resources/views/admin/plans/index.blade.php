@extends('layouts.admin')

@section('title', 'Planes')
@section('page-title', 'Planes')

@section('content')

<div class="flex items-center justify-between mb-5">
    <p class="text-sm text-slate-500">{{ $plans->count() }} planes configurados</p>
    <a href="{{ route('admin.plans.create') }}"
       class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition shadow-sm">
        <i class="fa-solid fa-plus"></i> Nuevo plan
    </a>
</div>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold text-slate-600">Plan</th>
                    <th class="text-right px-4 py-3 font-semibold text-slate-600">Precio (USD)</th>
                    <th class="text-right px-4 py-3 font-semibold text-slate-600 hidden md:table-cell">Máx. productos</th>
                    <th class="text-center px-4 py-3 font-semibold text-slate-600 hidden md:table-cell">Subscripciones</th>
                    <th class="text-center px-4 py-3 font-semibold text-slate-600">Estado</th>
                    <th class="text-center px-4 py-3 font-semibold text-slate-600">Destacado</th>
                    <th class="text-right px-4 py-3 font-semibold text-slate-600">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($plans as $plan)
                <tr class="hover:bg-slate-50 transition">
                    <td class="px-4 py-3">
                        <div class="font-medium text-slate-800">{{ $plan->name }}</div>
                        @if($plan->description)
                            <div class="text-xs text-slate-400 mt-0.5">{{ $plan->description }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right font-mono text-slate-700">
                        ${{ number_format($plan->price_usd, 2) }}
                    </td>
                    <td class="px-4 py-3 text-right hidden md:table-cell text-slate-600">
                        {{ $plan->maxProductsLabel() }}
                    </td>
                    <td class="px-4 py-3 text-center hidden md:table-cell text-slate-600">
                        {{ $plan->subscriptions_count }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($plan->active)
                            <span class="inline-flex items-center gap-1 text-xs font-medium bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">
                                <i class="fa-solid fa-circle text-[8px]"></i> Activo
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 text-xs font-medium bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full">
                                <i class="fa-solid fa-circle text-[8px]"></i> Inactivo
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($plan->featured)
                            <i class="fa-solid fa-star text-amber-400"></i>
                        @else
                            <span class="text-slate-300">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('admin.plans.edit', $plan) }}"
                               class="text-xs text-blue-600 hover:underline font-medium">Editar</a>
                            <form method="POST" action="{{ route('admin.plans.destroy', $plan) }}"
                                  onsubmit="return confirm('¿Eliminar el plan {{ $plan->name }}?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="text-xs text-red-600 hover:underline font-medium">Eliminar</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-10 text-center text-slate-400">
                        No hay planes configurados.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
