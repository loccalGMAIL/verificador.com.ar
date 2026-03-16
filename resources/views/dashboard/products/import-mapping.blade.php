@extends('layouts.app')

@section('title', 'Configurar importación')
@section('page-title', 'Configurar importación')

@section('content')

<div class="max-w-3xl">

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

        {{-- ── Tabla de mapeo ── --}}
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden mb-5">
            <div class="px-5 py-4 border-b border-slate-100">
                <h3 class="font-semibold text-slate-800 text-sm">Asignar columnas</h3>
                <p class="text-xs text-slate-400 mt-0.5">
                    Indicá qué representa cada columna de tu archivo. Las marcadas con
                    <span class="text-red-500 font-semibold">*</span> son obligatorias.
                    Seleccioná <em>"Ignorar"</em> para las columnas que no necesitás.
                </p>
            </div>

            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-2.5 text-left font-semibold text-slate-500 w-1/2">
                            Columna en el archivo
                        </th>
                        <th class="px-5 py-2.5 text-left font-semibold text-slate-500 w-1/2">
                            Campo del sistema
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($headers as $index => $header)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3">
                            <span class="font-mono text-xs bg-slate-100 text-slate-700 px-2 py-1 rounded">
                                {{ $header !== '' ? $header : "(vacío)" }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            <select name="mapping[{{ $index }}]"
                                    class="w-full border border-slate-200 rounded-lg px-3 py-1.5 text-sm
                                           focus:outline-none focus:ring-2 focus:ring-blue-400
                                           {{ in_array($autoMap[$index] ?? '', ['barcode','name']) ? 'border-blue-300 bg-blue-50' : '' }}">
                                <option value="">— Ignorar —</option>
                                @foreach($fields as $fieldKey => $fieldLabel)
                                    <option value="{{ $fieldKey }}"
                                        {{ ($autoMap[$index] ?? '') === $fieldKey ? 'selected' : '' }}>
                                        {{ $fieldLabel }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- ── Lista de precios destino ── --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5 mb-5">
            <h3 class="font-semibold text-slate-800 text-sm mb-1">Lista de precios destino</h3>
            <p class="text-xs text-slate-400 mb-3">
                Los precios del archivo se cargarán en la lista que elijas.
                Si no elegís ninguna, se usará la lista principal del comercio.
            </p>

            <select name="price_list_id"
                    class="w-full sm:w-72 border border-slate-300 rounded-lg px-3 py-2 text-sm
                           focus:outline-none focus:ring-2 focus:ring-blue-400">
                @foreach($priceLists as $list)
                    <option value="{{ $list->id }}"
                        {{ $list->is_default ? 'selected' : '' }}>
                        {{ $list->name }}
                        @if($list->is_default) (principal) @endif
                    </option>
                @endforeach
            </select>
        </div>

        {{-- ── Acciones ── --}}
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
