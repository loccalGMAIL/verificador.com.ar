@extends('layouts.app')

@section('title', 'Nueva lista de precios')
@section('page-title', 'Nueva lista de precios')

@section('content')
<div class="max-w-xl">
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <form method="POST" action="{{ route('dashboard.price-lists.store') }}" class="space-y-5">
            @csrf

            {{-- Nombre --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    Nombre de la lista <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" value="{{ old('name') }}" required maxlength="100"
                       placeholder="Ej: Mayorista, VIP, Empleados..."
                       class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm
                              focus:outline-none focus:ring-2 focus:ring-blue-500
                              @error('name') border-red-400 @enderror">
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Descripción --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Descripción <span class="text-slate-400 font-normal">(opcional)</span></label>
                <input type="text" name="description" value="{{ old('description') }}" maxlength="255"
                       placeholder="Ej: Precios para clientes mayoristas"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm
                              focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            {{-- ── Tipo de lista ─────────────────────────────────── --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Tipo de lista</label>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="type-cards">

                    {{-- Card: Manual --}}
                    <label id="card-manual"
                           class="relative flex flex-col gap-1 border-2 rounded-xl p-4 cursor-pointer transition
                                  border-blue-500 bg-blue-50">
                        <input type="radio" name="list_type" value="manual"
                               class="sr-only" checked
                               onchange="setListType('manual')">
                        <span class="flex items-center gap-2 font-semibold text-sm text-slate-800">
                            <i class="fa-solid fa-pencil text-blue-500"></i>
                            Manual
                        </span>
                        <span class="text-xs text-slate-500 leading-snug">
                            Los precios se cargan a mano o por importación CSV.
                        </span>
                    </label>

                    {{-- Card: Calculada --}}
                    <label id="card-calculated"
                           class="relative flex flex-col gap-1 border-2 rounded-xl p-4 cursor-pointer transition
                                  border-slate-200 bg-white hover:border-violet-300">
                        <input type="radio" name="list_type" value="calculated"
                               class="sr-only"
                               onchange="setListType('calculated')">
                        <span class="flex items-center gap-2 font-semibold text-sm text-slate-800">
                            <i class="fa-solid fa-calculator text-violet-500"></i>
                            Calculada por porcentaje
                        </span>
                        <span class="text-xs text-slate-500 leading-snug">
                            Los precios se calculan automáticamente desde otra lista aplicando un ajuste.
                        </span>
                    </label>
                </div>
            </div>

            {{-- ── Configuración de lista calculada ─────────────── --}}
            <div id="derived-config"
                 class="{{ old('base_price_list_id') ? '' : 'hidden' }}
                         bg-violet-50 border border-violet-200 rounded-xl p-4 space-y-4">

                <p class="text-xs font-semibold text-violet-700 uppercase tracking-wide">
                    <i class="fa-solid fa-calculator mr-1"></i>Configuración de cálculo
                </p>

                {{-- Lista base --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        Lista base <span class="text-red-500">*</span>
                    </label>
                    @if($manualLists->count() > 0)
                    <select name="base_price_list_id"
                            class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm bg-white
                                   focus:outline-none focus:ring-2 focus:ring-violet-500
                                   @error('base_price_list_id') border-red-400 @enderror">
                        <option value="">— Seleccioná una lista —</option>
                        @foreach($manualLists as $baseList)
                            <option value="{{ $baseList->id }}"
                                    {{ old('base_price_list_id') == $baseList->id ? 'selected' : '' }}>
                                {{ $baseList->name }}{{ $baseList->is_default ? ' (Principal)' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @else
                    <div class="text-sm text-slate-500 bg-white border border-slate-200 rounded-lg px-3 py-2.5">
                        No hay listas manuales todavía.
                        Primero creá una lista de tipo Manual.
                    </div>
                    @endif
                    @error('base_price_list_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Porcentaje --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        Ajuste porcentual <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center gap-3">
                        <input type="number" name="adjustment_pct"
                               value="{{ old('adjustment_pct', '') }}"
                               step="0.01" min="-99.99" max="999.99"
                               placeholder="Ej: -20  ó  +15"
                               class="w-36 border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-center bg-white
                                      focus:outline-none focus:ring-2 focus:ring-violet-500
                                      @error('adjustment_pct') border-red-400 @enderror">
                        <span class="text-slate-600 font-medium">%</span>
                    </div>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <span class="text-xs text-slate-500">Ejemplos:</span>
                        <button type="button" onclick="document.querySelector('[name=adjustment_pct]').value='-20'"
                                class="text-xs bg-red-50 text-red-600 px-2 py-0.5 rounded border border-red-100 hover:bg-red-100 transition">
                            -20% descuento
                        </button>
                        <button type="button" onclick="document.querySelector('[name=adjustment_pct]').value='15'"
                                class="text-xs bg-emerald-50 text-emerald-600 px-2 py-0.5 rounded border border-emerald-100 hover:bg-emerald-100 transition">
                            +15% recargo
                        </button>
                    </div>
                    <p class="text-xs text-slate-400 mt-1.5">
                        Valores <strong>negativos</strong> = descuento sobre la base.
                        Valores <strong>positivos</strong> = recargo sobre la base.
                    </p>
                    @error('adjustment_pct')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="bg-white rounded-lg border border-violet-200 px-3 py-2.5 text-xs text-violet-700">
                    <i class="fa-solid fa-arrows-rotate mr-1 text-violet-400"></i>
                    Los precios se recalculan <strong>automáticamente</strong> cada vez que modificás un precio en la lista base.
                </div>
            </div>

            {{-- Acciones --}}
            <div class="flex items-center gap-3 pt-1">
                <button type="submit"
                        class="bg-blue-600 text-white px-5 py-2.5 rounded-lg text-sm font-semibold hover:bg-blue-700 transition">
                    Crear lista
                </button>
                <a href="{{ route('dashboard.price-lists.index') }}"
                   class="text-slate-500 text-sm hover:text-slate-700">Cancelar</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function setListType(type) {
    const cardManual      = document.getElementById('card-manual');
    const cardCalculated  = document.getElementById('card-calculated');
    const derivedConfig   = document.getElementById('derived-config');

    if (type === 'calculated') {
        cardManual.classList.remove('border-blue-500', 'bg-blue-50');
        cardManual.classList.add('border-slate-200', 'bg-white');

        cardCalculated.classList.remove('border-slate-200', 'bg-white');
        cardCalculated.classList.add('border-violet-500', 'bg-violet-50');

        derivedConfig.classList.remove('hidden');
    } else {
        cardCalculated.classList.remove('border-violet-500', 'bg-violet-50');
        cardCalculated.classList.add('border-slate-200', 'bg-white');

        cardManual.classList.remove('border-slate-200', 'bg-white');
        cardManual.classList.add('border-blue-500', 'bg-blue-50');

        derivedConfig.classList.add('hidden');
        const sel = document.querySelector('[name="base_price_list_id"]');
        if (sel) sel.value = '';
        const pct = document.querySelector('[name="adjustment_pct"]');
        if (pct) pct.value = '';
    }
}

// Restaurar estado si hay old() values (error de validación)
@if(old('base_price_list_id'))
document.addEventListener('DOMContentLoaded', () => setListType('calculated'));
@endif
</script>
@endpush

@endsection
