@extends('layouts.app')

@section('title', 'Nueva lista de precios')
@section('page-title', 'Nueva lista de precios')

@section('content')
<div class="max-w-lg">
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <form method="POST" action="{{ route('dashboard.price-lists.store') }}" class="space-y-5" id="create-form">
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
                <label class="block text-sm font-medium text-slate-700 mb-1">Descripción</label>
                <input type="text" name="description" value="{{ old('description') }}" maxlength="255"
                       placeholder="Descripción opcional"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm
                              focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            {{-- Tipo de lista --}}
            <div class="border border-slate-200 rounded-lg overflow-hidden">
                <div class="bg-slate-50 px-4 py-3 border-b border-slate-200">
                    <p class="text-sm font-semibold text-slate-700">Tipo de lista</p>
                    <p class="text-xs text-slate-400 mt-0.5">¿Los precios se cargan manualmente o se calculan desde otra lista?</p>
                </div>
                <div class="p-4 space-y-3">

                    {{-- Opción: Manual --}}
                    <label class="flex items-start gap-3 cursor-pointer group">
                        <input type="radio" name="list_type" value="manual"
                               {{ old('base_price_list_id') ? '' : 'checked' }}
                               class="mt-0.5 accent-blue-600"
                               onchange="toggleDerivedFields(this.value)">
                        <div>
                            <p class="text-sm font-medium text-slate-700">Manual</p>
                            <p class="text-xs text-slate-400">Los precios se cargan a mano o por importación CSV.</p>
                        </div>
                    </label>

                    {{-- Opción: Calculada --}}
                    @if($manualLists->count() > 0)
                    <label class="flex items-start gap-3 cursor-pointer group">
                        <input type="radio" name="list_type" value="calculated"
                               {{ old('base_price_list_id') ? 'checked' : '' }}
                               class="mt-0.5 accent-violet-600"
                               onchange="toggleDerivedFields(this.value)">
                        <div>
                            <p class="text-sm font-medium text-slate-700">Calculada automáticamente</p>
                            <p class="text-xs text-slate-400">Los precios se derivan de otra lista aplicando un porcentaje de ajuste.</p>
                        </div>
                    </label>

                    {{-- Configuración de lista calculada --}}
                    <div id="derived-fields" class="{{ old('base_price_list_id') ? '' : 'hidden' }} pl-7 space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Lista base</label>
                            <select name="base_price_list_id"
                                    class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm
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
                            @error('base_price_list_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">
                                Ajuste porcentual
                            </label>
                            <div class="flex items-center gap-2">
                                <input type="number" name="adjustment_pct"
                                       value="{{ old('adjustment_pct', 0) }}"
                                       step="0.01" min="-99.99" max="999.99"
                                       class="w-28 border border-slate-300 rounded-lg px-3 py-2 text-sm text-right
                                              focus:outline-none focus:ring-2 focus:ring-violet-500
                                              @error('adjustment_pct') border-red-400 @enderror">
                                <span class="text-sm text-slate-500">%</span>
                            </div>
                            <p class="text-xs text-slate-400 mt-1">
                                Usá valores negativos para descuentos (<code class="bg-slate-100 px-1 rounded">-20</code> = −20%)
                                y positivos para recargos (<code class="bg-slate-100 px-1 rounded">+15</code> = +15%).
                            </p>
                            @error('adjustment_pct')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    @else
                    <p class="text-xs text-slate-400 pl-7">
                        No tenés listas manuales disponibles para usar como base.
                        <a href="{{ route('dashboard.price-lists.create') }}" class="text-blue-600 underline">Creá una primero.</a>
                    </p>
                    @endif

                </div>
            </div>

            {{-- Info --}}
            <div class="bg-blue-50 rounded-lg px-4 py-3 border border-blue-100 text-xs text-blue-700">
                <i class="fa-solid fa-circle-info mr-1"></i>
                Las listas calculadas actualizan sus precios automáticamente cada vez que modificás la lista base.
            </div>

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
function toggleDerivedFields(value) {
    const fields = document.getElementById('derived-fields');
    if (!fields) return;

    if (value === 'calculated') {
        fields.classList.remove('hidden');
        fields.querySelectorAll('input, select').forEach(el => el.removeAttribute('disabled'));
    } else {
        fields.classList.add('hidden');
        document.querySelector('[name="base_price_list_id"]').value = '';
        document.querySelector('[name="adjustment_pct"]').value = '0';
    }
}
</script>
@endpush

@endsection
