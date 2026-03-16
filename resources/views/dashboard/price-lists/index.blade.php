@extends('layouts.app')

@section('title', 'Listas de precios')
@section('page-title', 'Listas de precios')

@section('content')

<div class="flex items-center justify-between mb-5">
    @if($limit === null || $priceLists->count() < $limit)
        <a href="{{ route('dashboard.price-lists.create') }}"
           class="ml-auto flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-blue-700 transition">
            <i class="fa-solid fa-plus"></i>
            Nueva lista
        </a>
    @else
        <div class="ml-auto flex items-center gap-2 text-xs text-slate-500 bg-slate-50 border border-slate-200 px-3 py-2 rounded-lg">
            <i class="fa-solid fa-lock text-slate-400"></i>
            Límite de {{ $limit }} lista(s) alcanzado.
            <a href="{{ route('dashboard.subscription') }}" class="text-violet-600 font-medium underline">Mejorar plan</a>
        </div>
    @endif
</div>

<div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200">
            <tr class="text-left">
                <th class="px-4 py-3 font-semibold text-slate-500">Nombre</th>
                <th class="px-4 py-3 font-semibold text-slate-500 hidden sm:table-cell">Descripción</th>
                <th class="px-4 py-3 font-semibold text-slate-500">Productos</th>
                <th class="px-4 py-3 font-semibold text-slate-500">Estado</th>
                <th class="px-4 py-3 font-semibold text-slate-500 text-right">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($priceLists as $list)
            <tr class="hover:bg-slate-50 transition">
                <td class="px-4 py-4">
                    <div class="flex items-center gap-2">
                        <p class="font-medium text-slate-800">{{ $list->name }}</p>
                        @if($list->is_default)
                            <span class="text-xs bg-blue-50 text-blue-600 font-medium px-2 py-0.5 rounded-full">
                                Principal
                            </span>
                        @endif
                    </div>
                </td>
                <td class="px-4 py-4 text-slate-500 hidden sm:table-cell">
                    {{ $list->description ?: '—' }}
                </td>
                <td class="px-4 py-4">
                    <span class="font-semibold text-slate-700">{{ number_format($list->product_prices_count) }}</span>
                    <span class="text-slate-400 text-xs ml-1">con precio</span>
                </td>
                <td class="px-4 py-4">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                        {{ $list->active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                        {{ $list->active ? 'Activa' : 'Inactiva' }}
                    </span>
                </td>
                <td class="px-4 py-4 text-right">
                    <div class="flex items-center justify-end gap-1">
                        <a href="{{ route('dashboard.price-lists.edit', $list) }}"
                           class="text-blue-600 hover:text-blue-800 px-2 py-1 text-xs font-medium">
                            Editar precios
                        </a>
                        @if(!$list->is_default)
                        <form method="POST" action="{{ route('dashboard.price-lists.destroy', $list) }}"
                              class="inline"
                              onsubmit="return confirm('¿Eliminar la lista {{ addslashes($list->name) }}? Se perderán todos sus precios.')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="text-red-500 hover:text-red-700 px-2 py-1 text-xs font-medium">
                                Eliminar
                            </button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-4 py-12 text-center">
                    <i class="fa-solid fa-tags text-3xl text-slate-300 mb-3 block"></i>
                    <p class="text-slate-500 font-medium">No hay listas de precios.</p>
                    <p class="text-slate-400 text-xs mt-1">La lista General se crea automáticamente al registrarse.</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
