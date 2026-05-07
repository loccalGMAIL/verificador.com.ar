@extends('layouts.app')

@section('title', 'Campos personalizados')
@section('page-title', 'Campos personalizados')

@section('content')

{{-- Barra superior --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">

    <form method="GET" action="{{ route('dashboard.products.campos') }}"
          class="flex gap-2 flex-1 max-w-lg">
        <input type="text" name="q" value="{{ $search }}"
               placeholder="Buscar por nombre o código..."
               class="flex-1 border border-slate-300 rounded-lg px-3 py-2 text-sm
                      focus:outline-none focus:ring-2 focus:ring-blue-500">
        <button type="submit"
                class="bg-slate-700 text-white px-4 py-2 rounded-lg text-sm hover:bg-slate-800 transition">
            <i class="fa-solid fa-magnifying-glass"></i>
        </button>
    </form>

    <div class="flex gap-2">
        <a href="{{ route('dashboard.settings.custom-fields.index') }}"
           class="flex items-center gap-2 border border-slate-300 text-slate-700 px-4 py-2 rounded-lg text-sm hover:bg-slate-50 transition">
            <i class="fa-solid fa-gear"></i>
            <span class="hidden sm:inline">Gestionar campos</span>
        </a>
        <a href="{{ route('dashboard.products.index') }}"
           class="flex items-center gap-2 border border-slate-300 text-slate-700 px-4 py-2 rounded-lg text-sm hover:bg-slate-50 transition">
            <i class="fa-solid fa-arrow-left"></i>
            <span class="hidden sm:inline">Ver productos</span>
        </a>
    </div>
</div>

@if($definitions->isEmpty())

{{-- Sin definiciones --}}
<div class="bg-white rounded-xl border border-slate-200 px-6 py-16 text-center">
    <i class="fa-solid fa-table-columns text-4xl text-slate-300 mb-4 block"></i>
    <p class="text-slate-600 font-semibold mb-1">No hay campos personalizados definidos</p>
    <p class="text-slate-400 text-sm mb-5">
        Primero definí los campos en la configuración de tu comercio.
    </p>
    <a href="{{ route('dashboard.settings.custom-fields.index') }}"
       class="inline-flex items-center gap-2 bg-blue-600 text-white px-5 py-2.5 rounded-lg text-sm font-semibold hover:bg-blue-700 transition">
        <i class="fa-solid fa-plus"></i>
        Definir campos personalizados
    </a>
</div>

@else

{{-- Resumen --}}
<div class="mb-3 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500">
    <span>
        <strong class="text-slate-700">{{ $definitions->count() }}</strong>
        {{ $definitions->count() === 1 ? 'campo' : 'campos' }} definidos
    </span>
    <span class="text-slate-300">|</span>
    <span>
        <strong class="text-slate-700">{{ $products->total() }}</strong>
        productos
    </span>
    <span class="ml-auto flex items-center gap-1.5 text-slate-400 italic">
        <i class="fa-solid fa-circle text-amber-400 text-[8px]"></i>
        Celda vacía = sin dato importado
    </span>
</div>

{{-- Tabla matriz --}}
<div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm border-collapse">

            <thead class="bg-slate-50 border-b border-slate-200">
                <tr class="text-left">
                    <th class="px-4 py-3 font-semibold text-slate-500 whitespace-nowrap
                               sticky left-0 bg-slate-50 z-10 border-r border-slate-200 min-w-[200px]">
                        Producto
                    </th>
                    <th class="px-4 py-3 font-semibold text-slate-500 whitespace-nowrap
                               sticky left-[200px] bg-slate-50 z-10 border-r border-slate-200 min-w-[130px]">
                        Código
                    </th>
                    @foreach($definitions as $definition)
                    <th class="px-4 py-3 font-semibold text-slate-500 whitespace-nowrap min-w-[150px]">
                        {{ $definition->label }}
                        <span class="block text-[10px] font-normal text-slate-400 font-mono">{{ $definition->excel_column }}</span>
                    </th>
                    @endforeach
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100">
                @forelse($products as $product)
                @php $fields = $product->custom_fields ?? []; @endphp
                <tr class="group">

                    <td class="px-4 py-3 sticky left-0 bg-white group-hover:bg-slate-50 z-10
                               border-r border-slate-200 font-medium text-slate-800 max-w-[260px]">
                        <span class="block truncate">{{ $product->name }}</span>
                    </td>

                    <td class="px-4 py-3 sticky left-[200px] bg-white group-hover:bg-slate-50 z-10
                               border-r border-slate-200 font-mono text-slate-600 text-xs whitespace-nowrap">
                        {{ $product->barcode ?: '—' }}
                    </td>

                    @foreach($definitions as $definition)
                    @php $value = $fields[$definition->excel_column] ?? null; @endphp
                    @if($value !== null && $value !== '')
                    <td class="px-4 py-3 text-slate-700 group-hover:bg-slate-50">
                        {{ $value }}
                    </td>
                    @else
                    <td class="px-4 py-3 bg-amber-50 text-amber-400 italic text-xs group-hover:bg-amber-100">
                        vacío
                    </td>
                    @endif
                    @endforeach

                </tr>
                @empty
                <tr>
                    <td colspan="{{ 2 + $definitions->count() }}"
                        class="px-4 py-12 text-center">
                        <i class="fa-solid fa-box-open text-3xl text-slate-300 mb-3 block"></i>
                        <p class="text-slate-500 font-medium">No hay productos que coincidan.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>

        </table>
    </div>

    @if($products->hasPages())
    <div class="px-4 py-3 border-t border-slate-100">
        {{ $products->links() }}
    </div>
    @endif
</div>

@endif

@endsection
