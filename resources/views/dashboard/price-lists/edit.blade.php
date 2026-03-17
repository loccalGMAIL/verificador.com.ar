@extends('layouts.app')

@section('title', 'Editar lista: ' . $priceList->name)
@section('page-title', 'Lista de precios: ' . $priceList->name)

@section('content')

{{-- ── Formulario de configuración de la lista ──────────────────── --}}
<div class="bg-white rounded-xl border border-slate-200 p-5 mb-6">
    <form method="POST" action="{{ route('dashboard.price-lists.update', $priceList) }}"
          class="space-y-4">
        @csrf @method('PUT')

        {{-- Fila: nombre + descripción --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Nombre de la lista</label>
                <input type="text" name="name" value="{{ old('name', $priceList->name) }}"
                       required maxlength="100"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm
                              focus:outline-none focus:ring-2 focus:ring-blue-500
                              @error('name') border-red-400 @enderror">
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Descripción</label>
                <input type="text" name="description" value="{{ old('description', $priceList->description) }}"
                       maxlength="255"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm
                              focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        {{-- ── Panel de cálculo automático ──────────────────────── --}}
        <div class="border border-slate-200 rounded-xl overflow-hidden">
            <div class="flex items-center justify-between px-4 py-3 bg-slate-50 border-b border-slate-200">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-calculator text-slate-400 text-sm"></i>
                    <span class="text-sm font-semibold text-slate-700">Cálculo automático por porcentaje</span>
                </div>
                {{-- Toggle --}}
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="calc-toggle" class="sr-only peer"
                           {{ $priceList->isCalculated() ? 'checked' : '' }}
                           onchange="toggleCalcPanel(this.checked)">
                    <div class="w-9 h-5 bg-slate-200 peer-focus:ring-2 peer-focus:ring-violet-400 rounded-full peer
                                peer-checked:bg-violet-500 after:content-[''] after:absolute after:top-0.5 after:left-[2px]
                                after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all
                                peer-checked:after:translate-x-4"></div>
                </label>
            </div>

            <div id="calc-panel"
                 class="{{ $priceList->isCalculated() ? '' : 'hidden' }} p-4 space-y-4 bg-violet-50/50">

                {{-- Lista base --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        Lista base <span class="text-red-500">*</span>
                    </label>
                    @if($manualLists->count() > 0)
                    <select name="base_price_list_id"
                            class="w-full sm:w-72 border border-slate-300 rounded-lg px-3 py-2 text-sm bg-white
                                   focus:outline-none focus:ring-2 focus:ring-violet-500
                                   @error('base_price_list_id') border-red-400 @enderror">
                        <option value="">— Seleccioná una lista —</option>
                        @foreach($manualLists as $baseList)
                            <option value="{{ $baseList->id }}"
                                    {{ old('base_price_list_id', $priceList->base_price_list_id) == $baseList->id ? 'selected' : '' }}>
                                {{ $baseList->name }}{{ $baseList->is_default ? ' (Principal)' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @else
                    <p class="text-sm text-slate-500">No hay listas manuales disponibles para usar como base.</p>
                    @endif
                    @error('base_price_list_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Porcentaje --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Ajuste porcentual</label>
                    <div class="flex items-center gap-3">
                        <input type="number" name="adjustment_pct"
                               value="{{ old('adjustment_pct', $priceList->adjustment_pct) }}"
                               step="0.01" min="-99.99" max="999.99"
                               placeholder="Ej: -20  ó  +15"
                               class="w-36 border border-slate-300 rounded-lg px-3 py-2 text-sm text-center bg-white
                                      focus:outline-none focus:ring-2 focus:ring-violet-500
                                      @error('adjustment_pct') border-red-400 @enderror">
                        <span class="text-slate-600 font-medium text-sm">%</span>
                        <div class="flex gap-1.5">
                            <button type="button"
                                    onclick="document.querySelector('[name=adjustment_pct]').value='-20'"
                                    class="text-xs bg-red-50 text-red-600 px-2 py-1 rounded border border-red-100 hover:bg-red-100 transition">
                                -20%
                            </button>
                            <button type="button"
                                    onclick="document.querySelector('[name=adjustment_pct]').value='15'"
                                    class="text-xs bg-emerald-50 text-emerald-600 px-2 py-1 rounded border border-emerald-100 hover:bg-emerald-100 transition">
                                +15%
                            </button>
                        </div>
                    </div>
                    <p class="text-xs text-slate-400 mt-1">
                        Negativos = descuento. Positivos = recargo.
                        Ej: <code class="bg-slate-100 px-1 rounded">-20</code> → los precios de la lista base se reducen 20%.
                    </p>
                    @error('adjustment_pct')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Botón recalcular --}}
                @if($priceList->isCalculated())
                <div class="flex items-center gap-3 pt-1 border-t border-violet-100">
                    <form method="POST" action="{{ route('dashboard.price-lists.recalculate', $priceList) }}">
                        @csrf
                        <button type="submit"
                                class="flex items-center gap-1.5 bg-violet-600 text-white px-3 py-1.5 rounded-lg text-xs font-semibold hover:bg-violet-700 transition">
                            <i class="fa-solid fa-arrows-rotate"></i>
                            Recalcular todos los precios ahora
                        </button>
                    </form>
                    <span class="text-xs text-slate-400">
                        Los precios también se recalculan automáticamente al guardar cambios en la lista base.
                    </span>
                </div>
                @endif
            </div>
        </div>

        {{-- Activa / Inactiva --}}
        @if(!$priceList->is_default)
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="active" value="1"
                   {{ $priceList->active ? 'checked' : '' }}
                   class="w-4 h-4 accent-blue-600">
            <span class="text-sm text-slate-700">Lista activa</span>
        </label>
        @endif

        <div class="flex items-center gap-3 pt-1 border-t border-slate-100">
            <button type="submit"
                    class="bg-blue-600 text-white px-5 py-2 rounded-lg text-sm font-semibold hover:bg-blue-700 transition">
                <i class="fa-solid fa-floppy-disk mr-1.5"></i>Guardar configuración
            </button>
            <a href="{{ route('dashboard.price-lists.index') }}"
               class="text-slate-500 text-sm hover:text-slate-700">Cancelar</a>
        </div>
    </form>
</div>

{{-- ── PRECIOS ──────────────────────────────────────────────────────── --}}
@if($priceList->isCalculated())

{{-- Lista calculada — precios en solo lectura --}}
<div class="bg-white rounded-xl border border-slate-200 overflow-hidden mb-4">
    <div class="px-4 py-3 border-b border-slate-100 flex items-center gap-2">
        <i class="fa-solid fa-calculator text-violet-400 text-sm"></i>
        <div>
            <h3 class="font-semibold text-slate-800 text-sm">Precios calculados</h3>
            <p class="text-xs text-slate-400 mt-0.5">
                Calculados desde <strong>{{ $priceList->baseList?->name }}</strong>
                con ajuste {{ $priceList->adjustmentLabel() }}.
                Se actualizan automáticamente al cambiar la lista base.
            </p>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr class="text-left">
                    <th class="px-4 py-2.5 font-semibold text-slate-500">Producto</th>
                    <th class="px-4 py-2.5 font-semibold text-slate-500 hidden sm:table-cell">Código</th>
                    <th class="px-4 py-2.5 font-semibold text-slate-500 text-right">Precio ARS</th>
                    <th class="px-4 py-2.5 font-semibold text-slate-500 text-right">Precio USD</th>
                    <th class="px-4 py-2.5 font-semibold text-slate-500">Moneda</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($products as $product)
                @php $pp = $product->prices->first(); @endphp
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3 font-medium text-slate-800">{{ $product->name }}</td>
                    <td class="px-4 py-3 text-slate-500 font-mono text-xs hidden sm:table-cell">{{ $product->barcode }}</td>
                    <td class="px-4 py-3 text-right font-mono text-sm {{ $pp?->price_ars ? 'text-slate-800' : 'text-slate-300' }}">
                        {{ $pp?->price_ars ? '$ ' . number_format((float)$pp->price_ars, 2, ',', '.') : '—' }}
                    </td>
                    <td class="px-4 py-3 text-right font-mono text-sm {{ $pp?->price_usd ? 'text-slate-800' : 'text-slate-300' }}">
                        {{ $pp?->price_usd ? 'U$S ' . number_format((float)$pp->price_usd, 2, ',', '.') : '—' }}
                    </td>
                    <td class="px-4 py-3">
                        @if($pp)
                            <span class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded">{{ $pp->currency_default }}</span>
                        @else
                            <span class="text-xs text-slate-300">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-10 text-center text-slate-400 text-sm">
                        No hay precios calculados todavía.
                        Guardá la configuración de cálculo y hacé click en "Recalcular".
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($products->hasPages())
<div class="mb-4">{{ $products->links() }}</div>
@endif

@else

{{-- Lista manual — edición de precios --}}
<form method="POST" action="{{ route('dashboard.price-lists.prices', $priceList) }}">
    @csrf

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden mb-4">
        <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between gap-3">
            <div>
                <h3 class="font-semibold text-slate-800 text-sm">Precios de productos</h3>
                <p class="text-xs text-slate-400 mt-0.5">Dejá vacío para marcar el producto como "No disponible" en esta lista.</p>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <a href="{{ route('dashboard.products.import.index') }}"
                   class="flex items-center gap-1.5 bg-white border border-slate-300 text-slate-600 px-3 py-1.5 rounded-lg text-xs font-semibold hover:bg-slate-50 transition">
                    <i class="fa-solid fa-file-import"></i>
                    Importar
                </a>
                <button type="submit"
                        class="bg-emerald-600 text-white px-4 py-1.5 rounded-lg text-xs font-semibold hover:bg-emerald-700 transition">
                    <i class="fa-solid fa-floppy-disk mr-1"></i>Guardar precios
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr class="text-left">
                        <th class="px-4 py-2.5 font-semibold text-slate-500">Producto</th>
                        <th class="px-4 py-2.5 font-semibold text-slate-500 hidden sm:table-cell">Código</th>
                        <th class="px-4 py-2.5 font-semibold text-slate-500 text-right">Precio ARS</th>
                        <th class="px-4 py-2.5 font-semibold text-slate-500 text-right">Precio USD</th>
                        <th class="px-4 py-2.5 font-semibold text-slate-500">Moneda</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($products as $i => $product)
                    @php $pp = $product->prices->first(); @endphp
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3">
                            <p class="font-medium text-slate-800">{{ $product->name }}</p>
                            <input type="hidden" name="prices[{{ $i }}][product_id]" value="{{ $product->id }}">
                        </td>
                        <td class="px-4 py-3 text-slate-500 font-mono text-xs hidden sm:table-cell">{{ $product->barcode }}</td>
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
                                    class="border border-slate-200 rounded px-2 py-1 text-xs
                                           focus:outline-none focus:ring-1 focus:ring-blue-400">
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

    @if($products->hasPages())
    <div class="mb-4">{{ $products->links() }}</div>
    @endif

    @if($products->count() > 10)
    <div class="flex justify-end mb-4">
        <button type="submit"
                class="bg-emerald-600 text-white px-5 py-2.5 rounded-lg text-sm font-semibold hover:bg-emerald-700 transition">
            <i class="fa-solid fa-floppy-disk mr-1"></i>Guardar precios
        </button>
    </div>
    @endif

</form>

@endif

<div class="mt-2">
    <a href="{{ route('dashboard.price-lists.index') }}"
       class="text-slate-400 text-sm hover:text-slate-600">
        <i class="fa-solid fa-arrow-left mr-1"></i>Volver a listas
    </a>
</div>

@push('scripts')
<script>
function toggleCalcPanel(enabled) {
    const panel = document.getElementById('calc-panel');
    if (enabled) {
        panel.classList.remove('hidden');
    } else {
        panel.classList.add('hidden');
        // Limpiar campos al desactivar
        const sel = document.querySelector('[name="base_price_list_id"]');
        const pct = document.querySelector('[name="adjustment_pct"]');
        if (sel) sel.value = '';
        if (pct) pct.value = '';
    }
}
</script>
@endpush

@endsection
