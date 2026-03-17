@extends('layouts.app')

@section('title', 'Nuevo producto')
@section('page-title', 'Nuevo producto')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl border border-slate-200 p-6">

        <form method="POST" action="{{ route('dashboard.products.store') }}" enctype="multipart/form-data"
              class="space-y-5">
            @csrf

            {{-- Nombre --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    Nombre <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm
                              focus:outline-none focus:ring-2 focus:ring-blue-500
                              @error('name') border-red-400 @enderror"
                       placeholder="Ej: Leche La Serenísima 1L">
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Código de barras --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    Código de barras <span class="text-red-500">*</span>
                </label>
                <input type="text" name="barcode" value="{{ old('barcode') }}" required
                       class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm font-mono
                              focus:outline-none focus:ring-2 focus:ring-blue-500
                              @error('barcode') border-red-400 @enderror"
                       placeholder="7790001234567">
                @error('barcode')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Descripción --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Descripción</label>
                <textarea name="description" rows="2"
                          class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm
                                 focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="Descripción opcional del producto">{{ old('description') }}</textarea>
            </div>

            {{-- Precio --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Precio</label>
                <div class="relative">
                    <span class="absolute left-3 top-2.5 text-slate-400 text-sm">$</span>
                    <input type="number" step="0.01" min="0" name="price"
                           value="{{ old('price') }}" placeholder="0.00"
                           class="w-full border border-slate-300 rounded-lg pl-7 pr-3 py-2.5 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-blue-500
                                  @error('price') border-red-400 @enderror">
                </div>
                @error('price')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Imagen (opcional) --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    Imagen del producto <span class="text-slate-400 font-normal">(opcional)</span>
                </label>
                <input type="file" name="image" accept="image/*"
                       class="w-full text-sm text-slate-600 border border-slate-300 rounded-lg px-3 py-2
                              file:mr-3 file:border-0 file:bg-blue-50 file:text-blue-700
                              file:text-xs file:font-medium file:py-1 file:px-3 file:rounded-md">
                <p class="text-xs text-slate-400 mt-1">JPG, PNG o WebP. Máximo 2 MB.</p>
                @error('image')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Activo --}}
            <div class="flex items-center gap-2">
                <input type="hidden" name="active" value="0">
                <input type="checkbox" name="active" id="active" value="1"
                       class="rounded border-slate-300 text-blue-600"
                       {{ old('active', '1') ? 'checked' : '' }}>
                <label for="active" class="text-sm text-slate-700 cursor-pointer">
                    Producto activo (visible en consultas de precio)
                </label>
            </div>

            {{-- Botones --}}
            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="bg-blue-600 text-white px-6 py-2.5 rounded-lg text-sm font-semibold
                               hover:bg-blue-700 transition">
                    Guardar producto
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
