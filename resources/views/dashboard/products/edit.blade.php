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

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Precio ARS ($)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-slate-400 text-sm">$</span>
                        <input type="number" name="price_ars" step="0.01" min="0"
                               value="{{ old('price_ars', $product->price_ars) }}"
                               class="w-full border border-slate-300 rounded-lg pl-7 pr-3 py-2.5 text-sm
                                      focus:outline-none focus:ring-2 focus:ring-blue-500
                                      @error('price_ars') border-red-400 @enderror">
                    </div>
                    @error('price_ars')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Precio USD (U$S)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-slate-400 text-sm">U$S</span>
                        <input type="number" name="price_usd" step="0.01" min="0"
                               value="{{ old('price_usd', $product->price_usd) }}"
                               class="w-full border border-slate-300 rounded-lg pl-10 pr-3 py-2.5 text-sm
                                      focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    Moneda a mostrar <span class="text-red-500">*</span>
                </label>
                <select name="currency_default"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm
                               focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="ARS" @selected(old('currency_default', $product->currency_default) === 'ARS')>Pesos (ARS)</option>
                    <option value="USD" @selected(old('currency_default', $product->currency_default) === 'USD')>Dólares (USD)</option>
                </select>
            </div>

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
