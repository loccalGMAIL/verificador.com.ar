@extends('layouts.app')

@section('title', 'Configuración')
@section('page-title', 'Configuración')

@section('content')

@php
    $activeTab = request('tab', 'general');
@endphp

{{-- ── Pestañas ─────────────────────────────────────────── --}}
<div class="flex gap-1 mb-6 border-b border-slate-200 overflow-x-auto">
    @foreach([
        'general'      => ['icon' => 'fa-store',         'label' => 'General'],
        'excel-import' => ['icon' => 'fa-file-excel',    'label' => 'Importación Excel'],
        'print'        => ['icon' => 'fa-print',         'label' => 'Impresión QR'],
    ] as $tab => $meta)
    <a href="{{ route('dashboard.settings', ['tab' => $tab]) }}"
       class="flex items-center gap-1.5 px-4 py-2.5 text-sm font-medium whitespace-nowrap border-b-2 -mb-px transition
              {{ $activeTab === $tab
                    ? 'border-blue-600 text-blue-600'
                    : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
        <i class="fa-solid {{ $meta['icon'] }} text-xs"></i>
        {{ $meta['label'] }}
    </a>
    @endforeach
</div>

@if(session('success'))
<div class="mb-5 bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 text-sm text-emerald-700">
    <i class="fa-solid fa-circle-check mr-2"></i>{{ session('success') }}
</div>
@endif

{{-- ════════════════════════════════════════════════════════ --}}
{{-- TAB: General                                            --}}
{{-- ════════════════════════════════════════════════════════ --}}
@if($activeTab === 'general')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <form method="POST" action="{{ route('dashboard.settings.update') }}"
              enctype="multipart/form-data" class="space-y-5">
            @csrf @method('PUT')
            <input type="hidden" name="_tab" value="general">

            {{-- Logo --}}
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
                <p class="text-xs text-slate-400 mt-1">JPG, PNG o WebP. Máximo 2 MB.</p>
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

            {{-- Slug --}}
            <div class="bg-slate-50 rounded-lg px-4 py-3 border border-slate-200">
                <p class="text-xs text-slate-500">
                    <span class="font-semibold text-slate-600">ID de comercio:</span>
                    <span class="font-mono">{{ $store->slug }}</span>
                </p>
            </div>

            <div class="pt-2">
                <button type="submit"
                        class="bg-blue-600 text-white px-6 py-2.5 rounded-lg text-sm font-semibold
                               hover:bg-blue-700 transition">
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- ════════════════════════════════════════════════════════ --}}
{{-- TAB: Importación Excel                                  --}}
{{-- ════════════════════════════════════════════════════════ --}}
@if($activeTab === 'excel-import')
<div class="max-w-2xl space-y-6"
     x-data="{ showWholesale: {{ (old('show_wholesale') !== null ? old('show_wholesale') : ($store->show_wholesale ?? false)) ? 'true' : 'false' }} }">

    <form method="POST" action="{{ route('dashboard.settings.update') }}" class="space-y-6">
        @csrf @method('PUT')
        <input type="hidden" name="_tab" value="excel-import">

        {{-- Card: columnas del Excel --}}
        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <h3 class="font-semibold text-slate-800 mb-1">Columnas del archivo Excel / CSV</h3>
            <p class="text-xs text-slate-400 mb-5">
                Indicá exactamente los nombres de las columnas tal como aparecen en tu archivo (sin importar mayúsculas).
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Código de barras <span class="text-red-500">*</span></label>
                    <input type="text" name="excel_col_barcode"
                           value="{{ old('excel_col_barcode', $store->excel_col_barcode ?? 'codigo') }}"
                           required maxlength="100"
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-blue-500
                                  @error('excel_col_barcode') border-red-400 @enderror">
                    @error('excel_col_barcode')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Nombre <span class="text-red-500">*</span></label>
                    <input type="text" name="excel_col_name"
                           value="{{ old('excel_col_name', $store->excel_col_name ?? 'nombre') }}"
                           required maxlength="100"
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-blue-500
                                  @error('excel_col_name') border-red-400 @enderror">
                    @error('excel_col_name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Precio <span class="text-red-500">*</span></label>
                    <input type="text" name="excel_col_price"
                           value="{{ old('excel_col_price', $store->excel_col_price ?? 'precio') }}"
                           required maxlength="100"
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-blue-500
                                  @error('excel_col_price') border-red-400 @enderror">
                    @error('excel_col_price')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Card: visualización en escáner --}}
        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <h3 class="font-semibold text-slate-800 mb-1">Visualización en el escáner</h3>
            <p class="text-xs text-slate-400 mb-5">
                El precio secundario se calcula aplicando el descuento al precio principal.
            </p>

            <div class="space-y-4">
                <div class="max-w-xs">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Etiqueta precio principal <span class="text-red-500">*</span></label>
                    <input type="text" name="retail_label"
                           value="{{ old('retail_label', $store->retail_label ?? 'Precio') }}"
                           required maxlength="100"
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-blue-500
                                  @error('retail_label') border-red-400 @enderror">
                    @error('retail_label')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="show_wholesale" value="1"
                           x-model="showWholesale"
                           {{ (old('show_wholesale') !== null ? old('show_wholesale') : ($store->show_wholesale ?? false)) ? 'checked' : '' }}
                           class="w-4 h-4 text-blue-600 border-slate-300 rounded">
                    <span class="text-sm text-slate-700">Mostrar precio secundario (mayorista)</span>
                </label>

                <div x-show="showWholesale" class="grid grid-cols-1 sm:grid-cols-2 gap-4 pl-7">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Etiqueta precio secundario</label>
                        <input type="text" name="wholesale_label"
                               value="{{ old('wholesale_label', $store->wholesale_label ?? 'Mayorista') }}"
                               maxlength="100"
                               class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm
                                      focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Descuento % sobre precio principal</label>
                        <input type="number" name="wholesale_discount" step="0.01" min="0" max="100"
                               value="{{ old('wholesale_discount', $store->wholesale_discount ?? 0) }}"
                               class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm
                                      focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>
        </div>

        <div>
            <button type="submit"
                    class="bg-blue-600 text-white px-6 py-2.5 rounded-lg text-sm font-semibold
                           hover:bg-blue-700 transition">
                Guardar cambios
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush
@endif

{{-- ════════════════════════════════════════════════════════ --}}
{{-- TAB: Impresión QR                                       --}}
{{-- ════════════════════════════════════════════════════════ --}}
@if($activeTab === 'print')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <div class="flex items-start gap-4 mb-5">
            <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-print text-blue-500"></i>
            </div>
            <div>
                <h3 class="font-semibold text-slate-800">Configuración de impresión QR</h3>
                <p class="text-sm text-slate-500 mt-0.5">
                    La personalización del QR (colores, logo, textos) se configura individualmente para cada sucursal.
                </p>
            </div>
        </div>

        <a href="{{ route('dashboard.branches.index') }}"
           class="inline-flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-blue-700 transition">
            <i class="fa-solid fa-store"></i>
            Ir a sucursales
        </a>
    </div>
</div>
@endif

@endsection
