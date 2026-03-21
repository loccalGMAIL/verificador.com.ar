@extends('layouts.admin')

@section('title', 'Nuevo plan')
@section('page-title', 'Nuevo plan')

@section('content')

<div class="flex items-center gap-2 text-sm text-slate-500 mb-5">
    <a href="{{ route('admin.plans.index') }}" class="hover:text-blue-600 transition">Planes</a>
    <i class="fa-solid fa-chevron-right text-xs"></i>
    <span class="text-slate-800 font-medium">Nuevo plan</span>
</div>

<div class="max-w-lg bg-white rounded-xl border border-slate-200 shadow-sm p-6">
    <form method="POST" action="{{ route('admin.plans.store') }}" class="space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Nombre <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name') }}" required
                   class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none
                          @error('name') border-red-400 @enderror">
            @error('name')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Precio (USD) <span class="text-red-500">*</span></label>
                <input type="number" name="price_usd" value="{{ old('price_usd', '0') }}"
                       step="0.01" min="0" required
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none
                              @error('price_usd') border-red-400 @enderror">
                @error('price_usd')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Precio (ARS) <span class="text-red-500">*</span></label>
                <input type="number" name="price_ars" value="{{ old('price_ars', '0') }}"
                       step="0.01" min="0" required
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none
                              @error('price_ars') border-red-400 @enderror">
                @error('price_ars')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">
                Máx. productos <span class="text-slate-400 font-normal">(dejar vacío = ilimitados)</span>
            </label>
            <input type="number" name="max_products" value="{{ old('max_products') }}"
                   min="1" placeholder="Sin límite"
                   class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none
                          @error('max_products') border-red-400 @enderror">
            @error('max_products')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    Máx. sucursales <span class="text-slate-400 font-normal">(vacío = ilimitadas)</span>
                </label>
                <input type="number" name="max_branches" value="{{ old('max_branches') }}"
                       min="1" placeholder="Sin límite"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none
                              @error('max_branches') border-red-400 @enderror">
                @error('max_branches')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    Máx. listas de precios <span class="text-slate-400 font-normal">(vacío = ilimitadas)</span>
                </label>
                <input type="number" name="max_price_lists" value="{{ old('max_price_lists') }}"
                       min="1" placeholder="Sin límite"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none
                              @error('max_price_lists') border-red-400 @enderror">
                @error('max_price_lists')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Descripción</label>
            <textarea name="description" rows="2"
                      class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none resize-none
                             @error('description') border-red-400 @enderror">{{ old('description') }}</textarea>
            @error('description')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Orden de visualización <span class="text-red-500">*</span></label>
            <input type="number" name="sort_order" value="{{ old('sort_order', '0') }}"
                   min="0" required
                   class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none
                          @error('sort_order') border-red-400 @enderror">
            @error('sort_order')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex gap-6">
            <label class="flex items-center gap-2 text-sm text-slate-700 cursor-pointer select-none">
                <input type="checkbox" name="active" value="1"
                       {{ old('active', '1') ? 'checked' : '' }}
                       class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                Activo
            </label>
            <label class="flex items-center gap-2 text-sm text-slate-700 cursor-pointer select-none">
                <input type="checkbox" name="featured" value="1"
                       {{ old('featured') ? 'checked' : '' }}
                       class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                Destacado (recomendado)
            </label>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit"
                    class="flex items-center gap-2 px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition shadow-sm">
                <i class="fa-solid fa-floppy-disk"></i> Guardar plan
            </button>
            <a href="{{ route('admin.plans.index') }}"
               class="px-5 py-2 bg-slate-100 text-slate-600 text-sm font-medium rounded-lg hover:bg-slate-200 transition">
                Cancelar
            </a>
        </div>
    </form>
</div>

@endsection
