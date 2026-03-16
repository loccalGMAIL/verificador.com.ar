@extends('layouts.app')

@section('title', 'Configurar importación')
@section('page-title', 'Configurar importación')

@section('content')

<div class="max-w-3xl">

    {{-- Breadcrumb pasos --}}
    <div class="mb-4 flex items-center gap-2 text-xs text-slate-500">
        <span class="w-5 h-5 bg-slate-200 text-slate-600 rounded-full flex items-center justify-center font-semibold">1</span>
        <span class="text-slate-400">Subir archivo</span>
        <i class="fa-solid fa-chevron-right text-slate-300"></i>
        <span class="w-5 h-5 bg-blue-600 text-white rounded-full flex items-center justify-center font-semibold">2</span>
        <span class="font-semibold text-slate-700">Mapear columnas</span>
        <i class="fa-solid fa-chevron-right text-slate-300"></i>
        <span class="w-5 h-5 bg-slate-200 text-slate-600 rounded-full flex items-center justify-center font-semibold">3</span>
        <span class="text-slate-400">Importar</span>
    </div>

    @error('mapping')
    <div class="mb-4 bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-red-700 text-sm">
        <i class="fa-solid fa-circle-exclamation mr-2"></i>{{ $message }}
    </div>
    @enderror

    <form method="POST" action="{{ route('dashboard.products.import.mapping.store', $import) }}">
        @csrf

        {{-- ── Tabla de mapeo ─────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden mb-5">
            <div class="px-5 py-4 border-b border-slate-100">
                <h3 class="font-semibold text-slate-800 text-sm">Asignar columnas</h3>
                <p class="text-xs text-slate-400 mt-0.5">
                    Indicá qué representa cada columna de tu archivo. Las columnas marcadas en
                    <span class="text-blue-600 font-semibold">azul</span> fueron detectadas automáticamente.
                    Seleccioná <em>"Ignorar"</em> para columnas que no necesitás importar.
                </p>
            </div>

            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-2.5 text-left font-semibold text-slate-500 w-2/5">
                            Columna en el archivo
                        </th>
                        <th class="px-5 py-2.5 text-left font-semibold text-slate-500 w-3/5">
                            Campo del sistema
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($headers as $index => $header)
                    @php $detected = ($autoMap[$index] ?? '') !== ''; @endphp
                    <tr class="{{ $detected ? 'bg-blue-50/40' : 'hover:bg-slate-50' }}">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-xs {{ $detected ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-700' }} px-2 py-1 rounded">
                                    {{ $header !== '' ? $header : "(vacío)" }}
                                </span>
                                @if($detected)
                                    <i class="fa-solid fa-wand-magic-sparkles text-blue-400 text-[10px]" title="Detectado automáticamente"></i>
                                @endif
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            <select name="mapping[{{ $index }}]"
                                    class="w-full border rounded-lg px-3 py-1.5 text-sm
                                           focus:outline-none focus:ring-2 focus:ring-blue-400
                                           {{ $detected ? 'border-blue-300 bg-white' : 'border-slate-200' }}">
                                <option value="">— Ignorar —</option>

                                {{-- Campos base --}}
                                <optgroup label="Datos del producto">
                                    @foreach(['barcode' => 'Código de barras *', 'name' => 'Nombre *', 'desc' => 'Descripción', 'currency' => 'Moneda por defecto'] as $fKey => $fLabel)
                                        <option value="{{ $fKey }}"
                                            {{ ($autoMap[$index] ?? '') === $fKey ? 'selected' : '' }}>
                                            {{ $fLabel }}
                                        </option>
                                    @endforeach
                                </optgroup>

                                {{-- Campos de precio por lista --}}
                                @foreach($priceLists as $list)
                                    @if(!$list->isCalculated())
                                    <optgroup label="Precios — {{ $list->name }}{{ $list->is_default ? ' (principal)' : '' }}">
                                        <option value="price_list_{{ $list->id }}_ars"
                                            {{ ($autoMap[$index] ?? '') === "price_list_{$list->id}_ars" ? 'selected' : '' }}>
                                            Precio ARS — {{ $list->name }}
                                        </option>
                                        <option value="price_list_{{ $list->id }}_usd"
                                            {{ ($autoMap[$index] ?? '') === "price_list_{$list->id}_usd" ? 'selected' : '' }}>
                                            Precio USD — {{ $list->name }}
                                        </option>
                                    </optgroup>
                                    @endif
                                @endforeach

                            </select>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- ── Guardar como perfil ─────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5 mb-5">
            <label class="flex items-center gap-3 cursor-pointer" id="save-profile-toggle">
                <input type="checkbox" name="save_as_profile" value="1" id="save-profile-check"
                       class="w-4 h-4 accent-blue-600"
                       onchange="document.getElementById('profile-name-row').classList.toggle('hidden', !this.checked)">
                <div>
                    <p class="text-sm font-medium text-slate-700">Guardar este mapeo como perfil</p>
                    <p class="text-xs text-slate-400">La próxima vez podrás elegir este perfil y saltear el paso de mapeo.</p>
                </div>
            </label>
            <div id="profile-name-row" class="hidden mt-3">
                <input type="text" name="profile_name" placeholder="Nombre del perfil (ej: Proveedor Samsung)"
                       maxlength="100"
                       class="w-full sm:w-80 border border-slate-300 rounded-lg px-3 py-2 text-sm
                              focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        {{-- ── Acciones ────────────────────────────────────────────── --}}
        <div class="flex items-center gap-3">
            <button type="submit"
                    class="bg-blue-600 text-white px-6 py-2.5 rounded-lg text-sm font-semibold
                           hover:bg-blue-700 transition">
                <i class="fa-solid fa-play mr-1.5"></i>Iniciar importación
            </button>
            <a href="{{ route('dashboard.products.import.index') }}"
               class="text-slate-500 text-sm hover:text-slate-700">
                Cancelar
            </a>
        </div>

    </form>
</div>

@endsection
