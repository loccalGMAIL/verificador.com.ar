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
        'general'         => ['icon' => 'fa-store',          'label' => 'General'],
        'import-profiles' => ['icon' => 'fa-file-import',    'label' => 'Perfil de Importación'],
        'print'           => ['icon' => 'fa-print',          'label' => 'Configuración de Impresión'],
        'mobile'          => ['icon' => 'fa-mobile-screen',  'label' => 'Visual en Celular'],
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
@endif

{{-- ════════════════════════════════════════════════════════ --}}
{{-- TAB: Perfil de Importación                             --}}
{{-- ════════════════════════════════════════════════════════ --}}
@if($activeTab === 'import-profiles')
<div class="max-w-3xl space-y-6">

    <div class="bg-blue-50 border border-blue-100 rounded-xl px-4 py-3 text-sm text-blue-700">
        <i class="fa-solid fa-circle-info mr-2"></i>
        Los perfiles de importación guardan el mapeo de columnas de tu archivo para que no tengas que configurarlo cada vez.
        Al importar, elegís el perfil y el sistema lo aplica automáticamente.
    </div>

    {{-- Formulario nuevo perfil --}}
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <h3 class="font-semibold text-slate-800 text-sm mb-4">Nuevo perfil</h3>
        <form method="POST" action="{{ route('dashboard.settings.import-profiles.store') }}" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Nombre del perfil *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required maxlength="100"
                           placeholder="Ej: Proveedor Samsung, Planilla propia"
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-blue-500
                                  @error('name') border-red-400 @enderror">
                    @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Descripción</label>
                    <input type="text" name="description" value="{{ old('description') }}" maxlength="255"
                           placeholder="Descripción opcional"
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            {{-- Mapeo de encabezados --}}
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-2">
                    Mapeo de columnas
                    <span class="text-slate-400 font-normal">(nombre del encabezado en tu archivo → campo del sistema)</span>
                </label>
                <div class="space-y-2" id="profile-mapping-rows">
                    @php
                        $profileFields = [
                            'barcode'  => 'Código de barras *',
                            'name'     => 'Nombre *',
                            'desc'     => 'Descripción',
                            'currency' => 'Moneda por defecto',
                        ];
                        foreach(auth()->user()->store->priceLists()->where('active', true)->whereNull('base_price_list_id')->get() as $pl) {
                            $profileFields["price_list_{$pl->id}_ars"] = "Precio ARS — {$pl->name}";
                            $profileFields["price_list_{$pl->id}_usd"] = "Precio USD — {$pl->name}";
                        }
                    @endphp
                    @foreach($profileFields as $fieldKey => $fieldLabel)
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-slate-500 w-44 shrink-0">{{ $fieldLabel }}</span>
                        <span class="text-slate-300 text-xs">→</span>
                        <input type="text" name="header_mapping[{{ $fieldKey }}]"
                               value="{{ old("header_mapping.{$fieldKey}") }}"
                               placeholder="Nombre de columna en el archivo (ej: codigo_barras)"
                               class="flex-1 border border-slate-200 rounded px-3 py-1.5 text-xs
                                      focus:outline-none focus:ring-1 focus:ring-blue-400">
                    </div>
                    @endforeach
                </div>
                @error('header_mapping')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <button type="submit"
                    class="bg-blue-600 text-white px-5 py-2 rounded-lg text-sm font-semibold hover:bg-blue-700 transition">
                Crear perfil
            </button>
        </form>
    </div>

    {{-- Lista de perfiles existentes --}}
    @if($importProfiles->count() > 0)
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-100 bg-slate-50">
            <p class="text-sm font-semibold text-slate-700">Perfiles guardados</p>
        </div>
        @foreach($importProfiles as $profile)
        <div class="px-5 py-4 border-b border-slate-50 last:border-0">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-slate-800">{{ $profile->name }}</p>
                    @if($profile->description)
                        <p class="text-xs text-slate-400 mt-0.5">{{ $profile->description }}</p>
                    @endif
                    <div class="flex flex-wrap gap-1 mt-1.5">
                        @foreach($profile->header_mapping as $field => $header)
                        <span class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded font-mono">
                            {{ $header }}
                        </span>
                        @endforeach
                    </div>
                </div>
                <form method="POST"
                      action="{{ route('dashboard.settings.import-profiles.destroy', $profile) }}"
                      onsubmit="return confirm('¿Eliminar el perfil {{ addslashes($profile->name) }}?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-red-500 hover:text-red-700 text-xs font-medium">
                        Eliminar
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="bg-white rounded-xl border border-slate-200 px-5 py-10 text-center text-slate-400 text-sm">
        No hay perfiles guardados todavía.
    </div>
    @endif

</div>
@endif

{{-- ════════════════════════════════════════════════════════ --}}
{{-- TAB: Configuración de Impresión                        --}}
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

{{-- ════════════════════════════════════════════════════════ --}}
{{-- TAB: Visual en Celular                                  --}}
{{-- ════════════════════════════════════════════════════════ --}}
@if($activeTab === 'mobile')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl border border-slate-200 p-8 text-center">
        <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-4">
            <i class="fa-solid fa-mobile-screen text-2xl text-slate-400"></i>
        </div>
        <h3 class="font-semibold text-slate-700 mb-2">Personalización de la vista del escáner</h3>
        <p class="text-sm text-slate-400 max-w-sm mx-auto">
            Próximamente podrás personalizar los colores, logo y estilo de la pantalla que ven tus clientes al escanear el QR.
        </p>
        <span class="inline-block mt-4 text-xs bg-amber-100 text-amber-700 font-medium px-3 py-1 rounded-full">
            Próximamente
        </span>
    </div>
</div>
@endif

@endsection
