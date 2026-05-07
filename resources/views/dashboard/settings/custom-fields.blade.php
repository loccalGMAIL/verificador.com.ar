@extends('layouts.app')

@section('title', 'Configuración')
@section('page-title', 'Configuración')

@section('content')

@php $settingsActiveTab = 'custom-fields'; @endphp
@include('dashboard.settings._tabs')

<div class="max-w-3xl space-y-6">

    {{-- Listado de campos existentes --}}
    <div class="bg-white rounded-xl border border-slate-200">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="font-semibold text-slate-800">Campos definidos</h3>
                <p class="text-xs text-slate-400 mt-0.5">
                    La columna Excel debe coincidir exactamente con el encabezado en tu archivo (sin importar mayúsculas).
                </p>
            </div>
            <span class="text-xs text-slate-400">{{ $fields->count() }} {{ $fields->count() === 1 ? 'campo' : 'campos' }}</span>
        </div>

        @if($fields->isEmpty())
        <div class="px-6 py-10 text-center text-slate-400 text-sm">
            <i class="fa-solid fa-table-columns text-2xl mb-3 block opacity-40"></i>
            Aún no tenés campos personalizados. Creá el primero abajo.
        </div>
        @else
        <div class="divide-y divide-slate-100">
            @foreach($fields as $field)
            <div x-data="{ editing: false }" class="px-6 py-4">

                {{-- Vista normal --}}
                <div x-show="!editing" class="flex items-center gap-4">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-800">{{ $field->label }}</p>
                        <p class="text-xs text-slate-400 mt-0.5">
                            Columna Excel: <code class="bg-slate-100 px-1 rounded font-mono">{{ $field->excel_column }}</code>
                            &bull; Orden: {{ $field->sort_order }}
                        </p>
                    </div>
                    <div class="flex items-center gap-3 flex-shrink-0">
                        @if($field->visible_on_scan)
                            <span class="inline-flex items-center gap-1 text-xs font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-full px-2.5 py-0.5">
                                <i class="fa-solid fa-eye text-[10px]"></i>Visible en escaneo
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 text-xs font-medium text-slate-500 bg-slate-100 border border-slate-200 rounded-full px-2.5 py-0.5">
                                <i class="fa-solid fa-eye-slash text-[10px]"></i>Oculto
                            </span>
                        @endif
                        <button @click="editing = true"
                                class="text-xs text-blue-600 hover:text-blue-800 font-medium transition">
                            <i class="fa-solid fa-pencil mr-1"></i>Editar
                        </button>
                        <form method="POST" action="{{ route('dashboard.settings.custom-fields.destroy', $field) }}"
                              onsubmit="return confirm('¿Eliminar el campo \'{{ $field->label }}\'? Los datos importados en productos no se eliminarán.')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="text-xs text-red-500 hover:text-red-700 font-medium transition">
                                <i class="fa-solid fa-trash mr-1"></i>Eliminar
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Formulario de edición inline --}}
                <div x-show="editing" x-cloak>
                    <form method="POST" action="{{ route('dashboard.settings.custom-fields.update', $field) }}" class="space-y-3">
                        @csrf @method('PUT')
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">Etiqueta <span class="text-red-500">*</span></label>
                                <input type="text" name="label" value="{{ old('label', $field->label) }}"
                                       required maxlength="100"
                                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">Columna Excel <span class="text-red-500">*</span></label>
                                <input type="text" name="excel_column" value="{{ old('excel_column', $field->excel_column) }}"
                                       required maxlength="100"
                                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">Orden</label>
                                <input type="number" name="sort_order" value="{{ old('sort_order', $field->sort_order) }}"
                                       min="0" max="999"
                                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="hidden" name="visible_on_scan" value="0">
                            <input type="checkbox" id="visible_{{ $field->id }}" name="visible_on_scan" value="1"
                                   {{ old('visible_on_scan', $field->visible_on_scan) ? 'checked' : '' }}
                                   class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            <label for="visible_{{ $field->id }}" class="text-sm text-slate-700">Mostrar en la pantalla de escaneo del cliente</label>
                        </div>
                        <div class="flex gap-2 pt-1">
                            <button type="submit"
                                    class="bg-blue-600 text-white px-4 py-1.5 rounded-lg text-sm font-semibold hover:bg-blue-700 transition">
                                Guardar
                            </button>
                            <button type="button" @click="editing = false"
                                    class="text-slate-500 px-4 py-1.5 rounded-lg text-sm hover:text-slate-700 transition border border-slate-200">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Formulario para agregar nuevo campo --}}
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <h3 class="font-semibold text-slate-800 mb-1">Agregar nuevo campo</h3>
        <p class="text-xs text-slate-400 mb-4">
            El nombre de la columna Excel debe ser único y solo puede contener letras, números, guiones y guiones bajos.
        </p>

        <form method="POST" action="{{ route('dashboard.settings.custom-fields.store') }}" class="space-y-4">
            @csrf

            @if($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm text-red-700">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">
                        Etiqueta <span class="text-red-500">*</span>
                        <span class="text-slate-400 font-normal ml-1">— lo que verá el cliente</span>
                    </label>
                    <input type="text" name="label" value="{{ old('label') }}"
                           required maxlength="100" placeholder="Ej: Marca"
                           class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-blue-500
                                  @error('label') border-red-400 @enderror">
                    @error('label')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">
                        Columna Excel <span class="text-red-500">*</span>
                        <span class="text-slate-400 font-normal ml-1">— encabezado en el archivo</span>
                    </label>
                    <input type="text" name="excel_column" value="{{ old('excel_column') }}"
                           required maxlength="100" placeholder="Ej: marca"
                           class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm font-mono
                                  focus:outline-none focus:ring-2 focus:ring-blue-500
                                  @error('excel_column') border-red-400 @enderror">
                    @error('excel_column')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="flex items-center gap-2">
                <input type="hidden" name="visible_on_scan" value="0">
                <input type="checkbox" id="new_visible_on_scan" name="visible_on_scan" value="1"
                       {{ old('visible_on_scan', true) ? 'checked' : '' }}
                       class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                <label for="new_visible_on_scan" class="text-sm text-slate-700">
                    Mostrar en la pantalla de escaneo del cliente
                </label>
            </div>

            <div class="pt-1">
                <button type="submit"
                        class="bg-blue-600 text-white px-5 py-2.5 rounded-lg text-sm font-semibold
                               hover:bg-blue-700 transition">
                    <i class="fa-solid fa-plus mr-1.5"></i>Agregar campo
                </button>
            </div>
        </form>
    </div>

</div>

@endsection
