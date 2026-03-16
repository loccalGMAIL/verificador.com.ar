@extends('layouts.app')

@section('title', 'Editar lista: ' . $priceList->name)
@section('page-title', 'Lista de precios: ' . $priceList->name)

@section('content')

{{-- Datos de la lista --}}
<div class="bg-white rounded-xl border border-slate-200 p-5 mb-6">
    <form method="POST" action="{{ route('dashboard.price-lists.update', $priceList) }}"
          class="flex flex-col sm:flex-row gap-4 items-end">
        @csrf @method('PUT')

        <div class="flex-1">
            <label class="block text-xs font-medium text-slate-600 mb-1">Nombre de la lista</label>
            <input type="text" name="name" value="{{ old('name', $priceList->name) }}"
                   required maxlength="100"
                   class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm
                          focus:outline-none focus:ring-2 focus:ring-blue-500
                          @error('name') border-red-400 @enderror">
            @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="flex-1">
            <label class="block text-xs font-medium text-slate-600 mb-1">Descripción</label>
            <input type="text" name="description" value="{{ old('description', $priceList->description) }}"
                   maxlength="255"
                   class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm
                          focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        @if(!$priceList->is_default)
        <label class="flex items-center gap-2 cursor-pointer pb-2">
            <input type="checkbox" name="active" value="1"
                   {{ $priceList->active ? 'checked' : '' }}
                   class="w-4 h-4 accent-blue-600">
            <span class="text-sm text-slate-700">Activa</span>
        </label>
        @endif

        <button type="submit"
                class="flex-shrink-0 bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-blue-700 transition">
            Guardar
        </button>
    </form>
</div>

{{-- Tabla de precios por producto --}}
<form method="POST" action="{{ route('dashboard.price-lists.prices', $priceList) }}">
    @csrf

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden mb-4">
        <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="font-semibold text-slate-800 text-sm">Precios de productos</h3>
                <p class="text-xs text-slate-400 mt-0.5">Dejá vacío para marcar el producto como "No disponible" en esta lista.</p>
            </div>
            <button type="submit"
                    class="bg-emerald-600 text-white px-4 py-1.5 rounded-lg text-xs font-semibold hover:bg-emerald-700 transition">
                <i class="fa-solid fa-floppy-disk mr-1"></i>Guardar precios
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr class="text-left">
                        <th class="px-4 py-2.5 font-semibold text-slate-500">Producto</th>
                        <th class="px-4 py-2.5 font-semibold text-slate-500">Código</th>
                        <th class="px-4 py-2.5 font-semibold text-slate-500 text-right">Precio ARS</th>
                        <th class="px-4 py-2.5 font-semibold text-slate-500 text-right">Precio USD</th>
                        <th class="px-4 py-2.5 font-semibold text-slate-500">Moneda</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($products as $i => $product)
                    @php
                        $pp = $product->prices->first(); // precio en esta lista (eager loaded)
                    @endphp
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3">
                            <p class="font-medium text-slate-800">{{ $product->name }}</p>
                            <input type="hidden" name="prices[{{ $i }}][product_id]" value="{{ $product->id }}">
                        </td>
                        <td class="px-4 py-3 text-slate-500 font-mono text-xs">
                            {{ $product->barcode }}
                        </td>
                        <td class="px-4 py-3">
                            <input type="number" step="0.01" min="0"
                                   name="prices[{{ $i }}][price_ars]"
                                   value="{{ old("prices.{$i}.price_ars", $pp?->price_ars) }}"
                                   placeholder="—"
                                   class="w-28 border border-slate-200 rounded px-2 py-1 text-xs text-right
                                          focus:outline-none focus:ring-1 focus:ring-blue-400 ml-auto block">
                        </td>
                        <td class="px-4 py-3">
                            <input type="number" step="0.01" min="0"
                                   name="prices[{{ $i }}][price_usd]"
                                   value="{{ old("prices.{$i}.price_usd", $pp?->price_usd) }}"
                                   placeholder="—"
                                   class="w-24 border border-slate-200 rounded px-2 py-1 text-xs text-right
                                          focus:outline-none focus:ring-1 focus:ring-blue-400 ml-auto block">
                        </td>
                        <td class="px-4 py-3">
                            <select name="prices[{{ $i }}][currency_default]"
                                    class="border border-slate-200 rounded px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-blue-400">
                                <option value="ARS" {{ ($pp?->currency_default ?? 'ARS') === 'ARS' ? 'selected' : '' }}>ARS</option>
                                <option value="USD" {{ ($pp?->currency_default) === 'USD' ? 'selected' : '' }}>USD</option>
                            </select>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-slate-400 text-sm">
                            No hay productos activos en este comercio.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Paginación --}}
    @if($products->hasPages())
    <div class="mb-4">{{ $products->links() }}</div>
    @endif

    {{-- Botón guardar inferior (comodidad para listas largas) --}}
    @if($products->count() > 10)
    <div class="flex justify-end">
        <button type="submit"
                class="bg-emerald-600 text-white px-5 py-2.5 rounded-lg text-sm font-semibold hover:bg-emerald-700 transition">
            <i class="fa-solid fa-floppy-disk mr-1"></i>Guardar precios
        </button>
    </div>
    @endif

</form>

<div class="flex items-center gap-3 mt-4">
    <a href="{{ route('dashboard.price-lists.index') }}"
       class="text-slate-400 text-sm hover:text-slate-600">
        <i class="fa-solid fa-arrow-left mr-1"></i>Volver a listas
    </a>
</div>

@endsection
