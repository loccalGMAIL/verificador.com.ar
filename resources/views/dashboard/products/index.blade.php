@extends('layouts.app')

@section('title', 'Productos')
@section('page-title', 'Productos')

@section('content')

{{-- Barra superior: buscador + acciones --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
    <form method="GET" action="{{ route('dashboard.products.index') }}"
          class="flex gap-2 flex-1 max-w-lg">
        <input type="text" name="q" value="{{ $search }}" placeholder="Buscar por nombre o código..."
               class="flex-1 border border-slate-300 rounded-lg px-3 py-2 text-sm
                      focus:outline-none focus:ring-2 focus:ring-blue-500">
        <select name="currency"
                class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Moneda</option>
            <option value="ARS" @selected($currency === 'ARS')>ARS</option>
            <option value="USD" @selected($currency === 'USD')>USD</option>
        </select>
        <select name="status"
                class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Estado</option>
            <option value="1" @selected($status === '1')>Activos</option>
            <option value="0" @selected($status === '0')>Inactivos</option>
        </select>
        <button type="submit"
                class="bg-slate-700 text-white px-4 py-2 rounded-lg text-sm hover:bg-slate-800 transition">
            <i class="fa-solid fa-magnifying-glass"></i>
        </button>
    </form>

    <div class="flex gap-2">
        <a href="{{ route('dashboard.products.import.index') }}"
           class="flex items-center gap-2 border border-slate-300 text-slate-700 px-4 py-2 rounded-lg text-sm hover:bg-slate-50 transition">
            <i class="fa-solid fa-file-arrow-up"></i>
            <span class="hidden sm:inline">Importar</span>
        </a>
        <a href="{{ route('dashboard.products.create') }}"
           class="flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700 transition">
            <i class="fa-solid fa-plus"></i>
            <span>Nuevo producto</span>
        </a>
    </div>
</div>

{{-- Info del límite del plan --}}
@if($productLimit !== null)
<div class="mb-4 bg-slate-50 border border-slate-200 rounded-lg px-4 py-2.5 flex items-center justify-between text-sm">
    <span class="text-slate-600">
        Productos activos: <strong>{{ $productCount }}</strong> / {{ number_format($productLimit) }}
    </span>
    @if($productCount >= $productLimit)
        <span class="text-red-600 font-medium text-xs">Límite alcanzado</span>
    @else
        <span class="text-slate-400 text-xs">{{ $productLimit - $productCount }} disponibles</span>
    @endif
</div>
@endif

{{-- Tabla de productos --}}
<div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr class="text-left">
                    <th class="px-4 py-3 font-semibold text-slate-500">Imagen</th>
                    <th class="px-4 py-3 font-semibold text-slate-500">Producto</th>
                    <th class="px-4 py-3 font-semibold text-slate-500">Código</th>
                    <th class="px-4 py-3 font-semibold text-slate-500">Precio</th>
                    <th class="px-4 py-3 font-semibold text-slate-500">Estado</th>
                    <th class="px-4 py-3 font-semibold text-slate-500 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($products as $product)
                <tr class="hover:bg-slate-50 transition">
                    <td class="px-4 py-3">
                        @if($product->image_path)
                            <img src="{{ Storage::url($product->image_path) }}" alt=""
                                 class="w-10 h-10 rounded-lg object-cover border border-slate-200">
                        @else
                            <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center">
                                <i class="fa-solid fa-image text-slate-300 text-lg"></i>
                            </div>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <p class="font-medium text-slate-800">{{ $product->name }}</p>
                        @if($product->description)
                            <p class="text-xs text-slate-400 truncate max-w-xs">{{ $product->description }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3 font-mono text-slate-600 text-xs">{{ $product->barcode }}</td>
                    <td class="px-4 py-3 font-semibold text-slate-800">{{ $product->formattedPrice() }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                            {{ $product->active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                            {{ $product->active ? 'Activo' : 'Inactivo' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('dashboard.products.edit', $product) }}"
                           class="text-blue-600 hover:text-blue-800 px-2 py-1 text-xs font-medium">
                            Editar
                        </a>
                        <form method="POST" action="{{ route('dashboard.products.destroy', $product) }}"
                              class="inline"
                              onsubmit="return confirm('¿Eliminar {{ addslashes($product->name) }}?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="text-red-500 hover:text-red-700 px-2 py-1 text-xs font-medium">
                                Eliminar
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-12 text-center">
                        <i class="fa-solid fa-box-open text-3xl text-slate-300 mb-3 block"></i>
                        <p class="text-slate-500 font-medium">No hay productos todavía.</p>
                        <a href="{{ route('dashboard.products.create') }}"
                           class="text-blue-600 text-sm mt-2 inline-block hover:underline">
                            Crear el primero
                        </a>
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

@endsection
