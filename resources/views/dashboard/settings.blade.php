@extends('layouts.app')

@section('title', 'Configuración')
@section('page-title', 'Configuración del comercio')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl border border-slate-200 p-6">

        <form method="POST" action="{{ route('dashboard.settings.update') }}"
              enctype="multipart/form-data" class="space-y-5">
            @csrf @method('PUT')

            {{-- Logo actual --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Logo del comercio</label>
                @if($store->logo_path)
                <div class="mb-3 flex items-center gap-3">
                    <img src="{{ Storage::url($store->logo_path) }}" alt="Logo"
                         class="w-20 h-20 rounded-xl object-contain border border-slate-200 bg-slate-50 p-1">
                    <p class="text-xs text-slate-500">Logo actual. Subí uno nuevo para reemplazarlo.</p>
                </div>
                @endif
                <input type="file" name="logo" accept="image/*"
                       class="w-full text-sm text-slate-600 border border-slate-300 rounded-lg px-3 py-2
                              file:mr-3 file:border-0 file:bg-blue-50 file:text-blue-700
                              file:text-xs file:font-medium file:py-1 file:px-3 file:rounded-md
                              @error('logo') border-red-400 @enderror">
                <p class="text-xs text-slate-400 mt-1">JPG, PNG o WebP. Máximo 2 MB. Recomendado: fondo transparente.</p>
                @error('logo')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Nombre --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    Nombre del comercio <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" value="{{ old('name', $store->name) }}" required
                       class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm
                              focus:outline-none focus:ring-2 focus:ring-blue-500
                              @error('name') border-red-400 @enderror">
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Dirección --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Dirección principal</label>
                <input type="text" name="address" value="{{ old('address', $store->address) }}"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm
                              focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Av. Ejemplo 1234, Ciudad">
            </div>

            {{-- Teléfono --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Teléfono de contacto</label>
                <input type="text" name="phone" value="{{ old('phone', $store->phone) }}"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm
                              focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="+54 11 1234-5678">
            </div>

            {{-- Info slug --}}
            <div class="bg-slate-50 rounded-lg px-4 py-3 border border-slate-200">
                <p class="text-xs text-slate-500">
                    <span class="font-semibold text-slate-600">ID de comercio:</span>
                    <span class="font-mono">{{ $store->slug }}</span>
                </p>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="bg-blue-600 text-white px-6 py-2.5 rounded-lg text-sm font-semibold
                               hover:bg-blue-700 transition">
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
