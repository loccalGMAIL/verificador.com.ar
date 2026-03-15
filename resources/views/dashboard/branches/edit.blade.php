@extends('layouts.app')

@section('title', 'Editar sucursal')
@section('page-title', 'Editar sucursal')

@section('content')
<div class="max-w-lg">
    <div class="bg-white rounded-xl border border-slate-200 p-6">

        <form method="POST" action="{{ route('dashboard.branches.update', $branch) }}" class="space-y-5">
            @csrf @method('PUT')

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    Nombre de la sucursal <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" value="{{ old('name', $branch->name) }}" required
                       class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm
                              focus:outline-none focus:ring-2 focus:ring-blue-500
                              @error('name') border-red-400 @enderror">
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Dirección</label>
                <input type="text" name="address" value="{{ old('address', $branch->address) }}"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm
                              focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="flex items-center gap-2">
                <input type="hidden" name="active" value="0">
                <input type="checkbox" name="active" id="active" value="1"
                       class="rounded border-slate-300 text-blue-600"
                       {{ old('active', $branch->active) ? 'checked' : '' }}>
                <label for="active" class="text-sm text-slate-700 cursor-pointer">
                    Sucursal activa
                </label>
            </div>

            {{-- Info del QR --}}
            <div class="bg-slate-50 rounded-lg p-4 border border-slate-200 text-sm text-slate-600">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-medium text-slate-700 mb-0.5">Código QR</p>
                        <p class="text-xs text-slate-400 font-mono">{{ $branch->qr_token }}</p>
                    </div>
                    <a href="{{ route('dashboard.branches.qr', $branch) }}"
                       class="flex items-center gap-1.5 bg-emerald-600 text-white px-3 py-1.5 rounded-lg text-xs font-medium hover:bg-emerald-700 transition">
                        <i class="fa-solid fa-download"></i>
                        Descargar PNG
                    </a>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="bg-blue-600 text-white px-6 py-2.5 rounded-lg text-sm font-semibold
                               hover:bg-blue-700 transition">
                    Guardar cambios
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
