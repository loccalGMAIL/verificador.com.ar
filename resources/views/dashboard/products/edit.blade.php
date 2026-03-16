@extends('layouts.app')

@section('title', 'Editar producto')
@section('page-title', 'Editar producto')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl border border-slate-200 p-6">

        <form method="POST" action="{{ route('dashboard.products.update', $product) }}"
              enctype="multipart/form-data" class="space-y-5">
            @csrf @method('PUT')

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    Nombre <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" value="{{ old('name', $product->name) }}" required
                       class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm
                              focus:outline-none focus:ring-2 focus:ring-blue-500
                              @error('name') border-red-400 @enderror">
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    Código de barras <span class="text-red-500">*</span>
                </label>
                <input type="text" name="barcode" value="{{ old('barcode', $product->barcode) }}" required
                       class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm font-mono
                              focus:outline-none focus:ring-2 focus:ring-blue-500
                              @error('barcode') border-red-400 @enderror">
                @error('barcode')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Descripción</label>
                <textarea name="description" rows="2"
                          class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm
                                 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description', $product->description) }}</textarea>
            </div>

            {{-- ── Precios por lista ────────────────────────────── --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Precios</label>

                @forelse($priceLists as $list)
                @php $pp = $product->prices->firstWhere('price_list_id', $list->id); @endphp
                <div class="border border-slate-200 rounded-xl p-4 mb-3">
                    <div class="flex items-center gap-2 mb-3">
                        <i class="fa-solid fa-tags text-slate-400 text-xs"></i>
                        <span class="text-xs font-semibold text-slate-600 uppercase tracking-wide">
                            {{ $list->name }}
                            @if($list->is_default)
                                <span class="ml-1 text-blue-500 font-normal normal-case">(principal)</span>
                            @endif
                        </span>
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Precio ARS</label>
                            <div class="relative">
                                <span class="absolute left-2 top-2 text-slate-400 text-xs">$</span>
                                <input type="number" step="0.01" min="0"
                                       name="prices[{{ $list->id }}][price_ars]"
                                       value="{{ old("prices.{$list->id}.price_ars", $pp?->price_ars) }}"
                                       placeholder="—"
                                       class="w-full border border-slate-300 rounded-lg pl-5 pr-2 py-2 text-sm
                                              focus:outline-none focus:ring-2 focus:ring-blue-400">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Precio USD</label>
                            <div class="relative">
                                <span class="absolute left-2 top-2 text-slate-400 text-xs">U$S</span>
                                <input type="number" step="0.01" min="0"
                                       name="prices[{{ $list->id }}][price_usd]"
                                       value="{{ old("prices.{$list->id}.price_usd", $pp?->price_usd) }}"
                                       placeholder="—"
                                       class="w-full border border-slate-300 rounded-lg pl-9 pr-2 py-2 text-sm
                                              focus:outline-none focus:ring-2 focus:ring-blue-400">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Mostrar en</label>
                            <select name="prices[{{ $list->id }}][currency_default]"
                                    class="w-full border border-slate-300 rounded-lg px-2 py-2 text-sm
                                           focus:outline-none focus:ring-2 focus:ring-blue-400">
                                <option value="ARS" {{ old("prices.{$list->id}.currency_default", $pp?->currency_default ?? 'ARS') === 'ARS' ? 'selected' : '' }}>ARS</option>
                                <option value="USD" {{ old("prices.{$list->id}.currency_default", $pp?->currency_default) === 'USD' ? 'selected' : '' }}>USD</option>
                            </select>
                        </div>
                    </div>
                </div>
                @empty
                <p class="text-xs text-slate-400">No hay listas de precios activas.</p>
                @endforelse
            </div>

            <input type="hidden" name="price_ars" value="">
            <input type="hidden" name="price_usd" value="">
            <input type="hidden" name="currency_default" value="ARS">

            {{-- Imagen actual + nueva --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">
                    Imagen del producto <span class="text-slate-400 font-normal">(opcional)</span>
                </label>
                @if($product->image_path)
                <div class="mb-3 flex items-center gap-3">
                    <img src="{{ Storage::url($product->image_path) }}" alt=""
                         class="w-16 h-16 rounded-lg object-cover border border-slate-200">
                    <p class="text-xs text-slate-500">Imagen actual. Subí una nueva para reemplazarla.</p>
                </div>
                @endif
                <input type="file" name="image" accept="image/*"
                       class="w-full text-sm text-slate-600 border border-slate-300 rounded-lg px-3 py-2
                              file:mr-3 file:border-0 file:bg-blue-50 file:text-blue-700
                              file:text-xs file:font-medium file:py-1 file:px-3 file:rounded-md">
                <p class="text-xs text-slate-400 mt-1">JPG, PNG o WebP. Máximo 2 MB.</p>
                @error('image')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center gap-2">
                <input type="hidden" name="active" value="0">
                <input type="checkbox" name="active" id="active" value="1"
                       class="rounded border-slate-300 text-blue-600"
                       {{ old('active', $product->active) ? 'checked' : '' }}>
                <label for="active" class="text-sm text-slate-700 cursor-pointer">
                    Producto activo
                </label>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="bg-blue-600 text-white px-6 py-2.5 rounded-lg text-sm font-semibold
                               hover:bg-blue-700 transition">
                    Guardar cambios
                </button>
                <a href="{{ route('dashboard.products.index') }}"
                   class="text-slate-500 text-sm hover:text-slate-700 transition">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
