@extends('layouts.app')

@section('title', 'Nueva lista de precios')
@section('page-title', 'Nueva lista de precios')

@section('content')
<div class="max-w-lg">
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <form method="POST" action="{{ route('dashboard.price-lists.store') }}" class="space-y-4">
            @csrf

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

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Descripción</label>
                <input type="text" name="description" value="{{ old('description') }}" maxlength="255"
                       placeholder="Descripción opcional"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm
                              focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="bg-blue-50 rounded-lg px-4 py-3 border border-blue-100 text-xs text-blue-700">
                <i class="fa-solid fa-circle-info mr-1"></i>
                Después de crear la lista podrás cargar los precios de cada producto desde la pantalla de edición.
            </div>

            <div class="flex items-center gap-3 pt-2">
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
@endsection
