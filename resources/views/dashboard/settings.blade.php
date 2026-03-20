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
        'appearance'   => ['icon' => 'fa-palette',       'label' => 'Apariencia'],
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

@endif

{{-- ════════════════════════════════════════════════════════ --}}
{{-- TAB: Impresión QR                                       --}}
{{-- ════════════════════════════════════════════════════════ --}}

@if($activeTab === 'print')

@if($branches->isEmpty())

    {{-- Sin sucursales --}}
    <div class="max-w-md">
        <div class="bg-white rounded-xl border border-slate-200 p-8 text-center">
            <i class="fa-solid fa-store text-3xl text-slate-300 mb-3 block"></i>
            <p class="font-medium text-slate-700 mb-1">Todavía no tenés sucursales</p>
            <p class="text-sm text-slate-400 mb-5">Creá una sucursal para configurar e imprimir su QR.</p>
            <a href="{{ route('dashboard.branches.create') }}"
               class="inline-flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-blue-700 transition">
                <i class="fa-solid fa-plus"></i>
                Nueva sucursal
            </a>
        </div>
    </div>

@elseif($branches->count() > 1)

    {{-- Selector de sucursal --}}
    @php $selectedId = request('branch', $branches->first()->id); @endphp
    <div class="flex flex-wrap gap-2 mb-6">
        @foreach($branches as $b)
        <a href="{{ route('dashboard.settings', ['tab' => 'print', 'branch' => $b->id]) }}"
           class="px-4 py-2 rounded-lg border text-sm font-medium transition
                  {{ $b->id == $selectedId
                      ? 'bg-blue-600 text-white border-blue-600'
                      : 'bg-white text-slate-600 border-slate-200 hover:border-blue-400 hover:text-blue-600' }}">
            {{ $b->name }}
        </a>
        @endforeach
    </div>

    @php $branch = $branches->firstWhere('id', $selectedId) ?? $branches->first(); @endphp
    @include('dashboard.branches._qr-configure-form', ['branch' => $branch])

@else

    {{-- Una sola sucursal: mostrar configuración directamente --}}
    @include('dashboard.branches._qr-configure-form', ['branch' => $branches->first()])

@endif

@endif

{{-- ════════════════════════════════════════════════════════ --}}
{{-- TAB: Apariencia                                         --}}
{{-- ════════════════════════════════════════════════════════ --}}
@if($activeTab === 'appearance')
@php
    $logoUrl = $store->logo_path ? Storage::url($store->logo_path) : null;
@endphp
<div x-data="{
        open:                null,
        bgColor:             '{{ old('scan_bg_color',              $store->scan_bg_color              ?? '#0f172a') }}',
        accentColor:         '{{ old('scan_accent_color',          $store->scan_accent_color          ?? '#34d399') }}',
        secondaryColor:      '{{ old('scan_secondary_color',       $store->scan_secondary_color       ?? '#93c5fd') }}',
        wholesaleCardColor:  '{{ old('scan_wholesale_card_color',  $store->scan_wholesale_card_color  ?? '#172033') }}',
        cardStyle:           '{{ old('scan_card_style',            $store->scan_card_style            ?? 'dark') }}',
        fontSize:            '{{ old('scan_font_size',             $store->scan_font_size             ?? 'lg') }}',
        showLogo:            {{ (old('scan_show_logo')         !== null ? old('scan_show_logo')         : ($store->scan_show_logo         ?? false)) ? 'true' : 'false' }},
        showStoreName:       {{ (old('scan_show_store_name')   !== null ? old('scan_show_store_name')   : ($store->scan_show_store_name   ?? true))  ? 'true' : 'false' }},
        showBranchName:      {{ (old('scan_show_branch_name')  !== null ? old('scan_show_branch_name')  : ($store->scan_show_branch_name  ?? true))  ? 'true' : 'false' }},
        headerText:          '{{ old('scan_header_text', $store->scan_header_text ?? 'Consultá el precio') }}',
        logoUrl:             '{{ $logoUrl }}',
        get cardBg()     { return this.cardStyle === 'light' ? '#f1f5f9' : '#1e293b'; },
        get cardBorder() { return this.cardStyle === 'light' ? '#cbd5e1' : '#334155'; },
        get cardText()   { return this.cardStyle === 'light' ? '#1e293b' : '#ffffff'; },
     }">

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 items-start">

        {{-- ── Formulario en acordeón ───────────────────────────── --}}
        <form method="POST" action="{{ route('dashboard.settings.update') }}">
            @csrf @method('PUT')
            <input type="hidden" name="_tab" value="appearance">

            <div class="space-y-2">

                {{-- ── Colores ──────────────────────────────────── --}}
                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                    <button type="button" @click="open = open === 'colores' ? null : 'colores'"
                            class="w-full flex items-center justify-between px-5 py-3.5 text-left">
                        <span class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                            <i class="fa-solid fa-droplet w-4 text-center text-slate-400"></i>
                            Colores
                        </span>
                        <i class="fa-solid fa-chevron-down text-xs text-slate-400 transition-transform duration-200"
                           :class="open === 'colores' ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="open === 'colores'" x-transition class="px-5 pb-5 border-t border-slate-100">
                        <div class="grid grid-cols-2 gap-4 pt-4">

                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1.5">Color de fondo</label>
                                <div class="flex items-center gap-2">
                                    <input type="color" name="scan_bg_color" x-model="bgColor"
                                           class="w-10 h-10 rounded-lg border border-slate-300 cursor-pointer p-0.5">
                                    <input type="text" x-model="bgColor"
                                           class="w-28 border border-slate-200 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-400"
                                           maxlength="7" placeholder="#0f172a">
                                </div>
                                @error('scan_bg_color')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1.5">Color precio principal</label>
                                <div class="flex items-center gap-2">
                                    <input type="color" name="scan_accent_color" x-model="accentColor"
                                           class="w-10 h-10 rounded-lg border border-slate-300 cursor-pointer p-0.5">
                                    <input type="text" x-model="accentColor"
                                           class="w-28 border border-slate-200 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-400"
                                           maxlength="7" placeholder="#34d399">
                                </div>
                                @error('scan_accent_color')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1.5">Color precio secundario (texto)</label>
                                <div class="flex items-center gap-2">
                                    <input type="color" name="scan_secondary_color" x-model="secondaryColor"
                                           class="w-10 h-10 rounded-lg border border-slate-300 cursor-pointer p-0.5">
                                    <input type="text" x-model="secondaryColor"
                                           class="w-28 border border-slate-200 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-400"
                                           maxlength="7" placeholder="#93c5fd">
                                </div>
                                @error('scan_secondary_color')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1.5">Color fondo tarjeta mayorista</label>
                                <div class="flex items-center gap-2">
                                    <input type="color" name="scan_wholesale_card_color" x-model="wholesaleCardColor"
                                           class="w-10 h-10 rounded-lg border border-slate-300 cursor-pointer p-0.5">
                                    <input type="text" x-model="wholesaleCardColor"
                                           class="w-28 border border-slate-200 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-400"
                                           maxlength="7" placeholder="#172033">
                                </div>
                                @error('scan_wholesale_card_color')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>

                        </div>
                    </div>
                </div>

                {{-- ── Tipografía y tarjetas ────────────────────── --}}
                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                    <button type="button" @click="open = open === 'tipo' ? null : 'tipo'"
                            class="w-full flex items-center justify-between px-5 py-3.5 text-left">
                        <span class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                            <i class="fa-solid fa-text-height w-4 text-center text-slate-400"></i>
                            Tipografía y tarjetas
                        </span>
                        <i class="fa-solid fa-chevron-down text-xs text-slate-400 transition-transform duration-200"
                           :class="open === 'tipo' ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="open === 'tipo'" x-transition class="px-5 pb-5 border-t border-slate-100">
                        <div class="space-y-4 pt-4">

                            <div>
                                <p class="text-xs font-medium text-slate-600 mb-2">Tamaño del precio</p>
                                <div class="flex gap-1">
                                    @foreach(['sm' => 'Pequeño', 'md' => 'Mediano', 'lg' => 'Grande', 'xl' => 'Muy gde.'] as $val => $lbl)
                                    <label class="flex-1 cursor-pointer relative">
                                        <input type="radio" name="scan_font_size" value="{{ $val }}" class="sr-only"
                                               x-model="fontSize">
                                        <div class="size-pill border border-slate-200 rounded-lg py-1.5 text-center text-xs font-medium
                                                    text-slate-500 hover:border-blue-400 hover:text-blue-600 transition select-none">
                                            {{ $lbl }}
                                        </div>
                                    </label>
                                    @endforeach
                                </div>
                                @error('scan_font_size')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <p class="text-xs font-medium text-slate-600 mb-2">Estilo de tarjetas</p>
                                <div class="flex gap-1">
                                    @foreach(['dark' => 'Oscuro', 'light' => 'Claro'] as $val => $lbl)
                                    <label class="flex-1 cursor-pointer relative">
                                        <input type="radio" name="scan_card_style" value="{{ $val }}" class="sr-only"
                                               x-model="cardStyle">
                                        <div class="size-pill border border-slate-200 rounded-lg py-1.5 text-center text-xs font-medium
                                                    text-slate-500 hover:border-blue-400 hover:text-blue-600 transition select-none">
                                            {{ $lbl }}
                                        </div>
                                    </label>
                                    @endforeach
                                </div>
                                @error('scan_card_style')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>

                        </div>
                    </div>
                </div>

                {{-- ── Encabezado y visibilidad ─────────────────── --}}
                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                    <button type="button" @click="open = open === 'header' ? null : 'header'"
                            class="w-full flex items-center justify-between px-5 py-3.5 text-left">
                        <span class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                            <i class="fa-solid fa-eye w-4 text-center text-slate-400"></i>
                            Encabezado y visibilidad
                        </span>
                        <i class="fa-solid fa-chevron-down text-xs text-slate-400 transition-transform duration-200"
                           :class="open === 'header' ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="open === 'header'" x-transition class="px-5 pb-5 border-t border-slate-100">
                        <div class="space-y-4 pt-4">

                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1.5">Texto del encabezado</label>
                                <input type="text" name="scan_header_text" x-model="headerText"
                                       maxlength="100"
                                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400
                                              @error('scan_header_text') border-red-400 @enderror"
                                       placeholder="Consultá el precio">
                                @error('scan_header_text')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>

                            <label class="flex items-center justify-between cursor-pointer">
                                <span class="text-sm text-slate-700">Mostrar logo del comercio</span>
                                <div class="relative">
                                    <input type="checkbox" name="scan_show_logo" value="1"
                                           x-model="showLogo" class="sr-only">
                                    <div class="toggle-track w-11 h-6 bg-slate-200 rounded-full transition"></div>
                                    <div class="toggle-dot absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow transition"></div>
                                </div>
                            </label>
                            @if(!$store->logo_path)
                            <p class="text-xs text-slate-400 -mt-2">
                                <i class="fa-solid fa-info-circle mr-1"></i>
                                Subí tu logo en la pestaña General primero.
                            </p>
                            @endif

                            <label class="flex items-center justify-between cursor-pointer">
                                <span class="text-sm text-slate-700">Mostrar nombre del comercio</span>
                                <div class="relative">
                                    <input type="checkbox" name="scan_show_store_name" value="1"
                                           x-model="showStoreName" class="sr-only">
                                    <div class="toggle-track w-11 h-6 bg-slate-200 rounded-full transition"></div>
                                    <div class="toggle-dot absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow transition"></div>
                                </div>
                            </label>

                            <label class="flex items-center justify-between cursor-pointer">
                                <span class="text-sm text-slate-700">Mostrar nombre de la sucursal</span>
                                <div class="relative">
                                    <input type="checkbox" name="scan_show_branch_name" value="1"
                                           x-model="showBranchName" class="sr-only">
                                    <div class="toggle-track w-11 h-6 bg-slate-200 rounded-full transition"></div>
                                    <div class="toggle-dot absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow transition"></div>
                                </div>
                            </label>

                        </div>
                    </div>
                </div>

                {{-- Guardar --}}
                <div class="pt-1">
                    <button type="submit"
                            class="w-full flex items-center justify-center gap-2 bg-blue-600 text-white font-semibold
                                   py-3 rounded-xl hover:bg-blue-700 active:scale-95 transition text-sm">
                        <i class="fa-solid fa-floppy-disk"></i>
                        Guardar cambios
                    </button>
                </div>

            </div>
        </form>

        {{-- ── Preview celular ──────────────────────────────────── --}}
        <div class="flex flex-col items-center xl:sticky xl:top-4">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">Vista previa</p>

            <div class="relative mx-auto" style="width: 240px;">
                <div class="rounded-[2rem] overflow-hidden shadow-2xl ring-[6px] ring-slate-800"
                     style="height: 480px;">
                    <div class="h-full flex flex-col overflow-hidden"
                         :style="{ backgroundColor: bgColor }">

                        {{-- Header --}}
                        <div class="px-3 py-2.5 flex flex-col gap-0.5">
                            <div class="flex items-center gap-1.5">
                                <template x-if="showLogo && logoUrl">
                                    <img :src="logoUrl" alt="Logo" class="h-5 max-w-[80px] object-contain">
                                </template>
                                <template x-if="!(showLogo && logoUrl)">
                                    <div class="flex items-center gap-1">
                                        <svg viewBox="0 0 36 36" class="w-4 h-4 flex-none" aria-hidden="true">
                                            <circle cx="18" cy="18" r="14" fill="white" stroke="#2563eb" stroke-width="2.5"/>
                                            <path d="M11 19 L16 24 L33 8" fill="none" stroke="#10b981" stroke-width="4"
                                                  stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        <span class="text-[9px] font-semibold text-slate-300">verificador.com.ar</span>
                                    </div>
                                </template>
                            </div>
                            <template x-if="showStoreName">
                                <p class="text-[9px] font-bold text-white leading-tight">{{ $store->name }}</p>
                            </template>
                            <template x-if="showBranchName">
                                <p class="text-[8px] text-slate-400 leading-tight">Sucursal Centro</p>
                            </template>
                        </div>

                        {{-- Título --}}
                        <div class="px-3 pb-2 text-center">
                            <p class="text-xs font-bold text-white leading-tight" x-text="headerText || 'Consultá el precio'"></p>
                            <p class="text-[9px] text-slate-400 mt-0.5">Apuntá la cámara al código de barras</p>
                        </div>

                        {{-- Simulación cámara --}}
                        <div class="mx-3 rounded-lg bg-black h-12 flex items-center justify-center mb-2">
                            <i class="fa-solid fa-camera text-slate-600 text-base"></i>
                        </div>

                        {{-- Nombre producto fake --}}
                        <div class="px-3 mb-1.5 text-center">
                            <p class="text-[9px] text-white font-bold leading-snug">Fideos Spaghetti N°5 x 500g</p>
                        </div>

                        {{-- Precio principal fake --}}
                        <div class="mx-3 rounded-xl px-3 py-2 mb-1.5"
                             :style="{ backgroundColor: cardBg, border: '1px solid ' + cardBorder }">
                            <p class="text-[8px] font-semibold uppercase tracking-wide mb-0.5"
                               :style="{ color: cardText, opacity: 0.6 }">
                                <i class="fa-solid fa-tags mr-0.5"></i>Precio
                            </p>
                            <p class="font-black leading-none"
                               :class="{
                                   'text-lg':  fontSize === 'sm',
                                   'text-xl':  fontSize === 'md',
                                   'text-2xl': fontSize === 'lg',
                                   'text-3xl': fontSize === 'xl'
                               }"
                               :style="{ color: accentColor }">$ 1.250,00</p>
                        </div>

                        {{-- Precio secundario fake --}}
                        <div class="mx-3 rounded-xl px-3 py-1.5"
                             :style="{ backgroundColor: wholesaleCardColor, border: '1px solid ' + cardBorder }">
                            <p class="text-[8px] font-semibold uppercase tracking-wide mb-0.5"
                               :style="{ color: cardText, opacity: 0.6 }">
                                <i class="fa-solid fa-tags mr-0.5"></i>Mayorista
                            </p>
                            <p class="font-black leading-none"
                               :class="{
                                   'text-lg':  fontSize === 'sm',
                                   'text-xl':  fontSize === 'md',
                                   'text-2xl': fontSize === 'lg',
                                   'text-3xl': fontSize === 'xl'
                               }"
                               :style="{ color: secondaryColor }">$ 1.000,00</p>
                        </div>

                    </div>
                </div>
                {{-- Notch decorativo --}}
                <div class="absolute top-3 left-1/2 -translate-x-1/2 w-16 h-1.5 bg-slate-800 rounded-full"></div>
            </div>
        </div>

    </div>
</div>

@endif

@endsection

@push('styles')
<style>
    /* Toggles */
    input:checked + .toggle-track { background-color: #10b981; }
    input:checked ~ .toggle-dot   { transform: translateX(20px); }
    .toggle-track, .toggle-dot    { transition: all .2s; }

    /* Pills de selección */
    input[type="radio"]:checked + .size-pill {
        border-color: #3b82f6 !important;
        color: #2563eb !important;
        background-color: #eff6ff;
    }
</style>
@endpush
