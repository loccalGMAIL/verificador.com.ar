@extends('layouts.app')

@section('title', 'Nueva sucursal')
@section('page-title', 'Nueva sucursal')

@section('content')
<div class="max-w-lg">
    <div class="bg-white rounded-xl border border-slate-200 p-6">

        <p class="text-sm text-slate-500 mb-5">
            Al crear la sucursal se genera automáticamente un código QR único.
            Descargalo desde la lista de sucursales para imprimirlo.
        </p>

        <form method="POST" action="{{ route('dashboard.branches.store') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    Nombre de la sucursal <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm
                              focus:outline-none focus:ring-2 focus:ring-blue-500
                              @error('name') border-red-400 @enderror"
                       placeholder="Ej: Casa central, Sucursal Norte">
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Dirección</label>
                <input type="text" name="address" value="{{ old('address') }}"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm
                              focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Ej: Av. Corrientes 1234, CABA">
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="bg-blue-600 text-white px-6 py-2.5 rounded-lg text-sm font-semibold
                               hover:bg-blue-700 transition">
                    Crear sucursal
                </button>
                <a href="{{ route('dashboard.branches.index') }}"
                   class="text-slate-500 text-sm hover:text-slate-700 transition">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
