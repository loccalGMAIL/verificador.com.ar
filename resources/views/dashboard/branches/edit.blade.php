@extends('layouts.app')

@section('title', 'Editar sucursal')
@section('page-title', 'Editar sucursal')

@section('content')
<div class="max-w-lg">
    <div class="bg-white rounded-xl border border-slate-200 p-6">

        <form method="POST" action="{{ route('dashboard.branches.update', $branch) }}" class="space-y-5"
              enctype="multipart/form-data">
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

            {{-- Imagen promocional --}}
            <div class="border-t border-slate-200 pt-5 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Imagen promocional
                    </label>

                    @if($branch->promo_image_path)
                    <div class="mb-3 flex items-start gap-3">
                        <img src="{{ Storage::disk('public')->url($branch->promo_image_path) }}"
                             alt="Promo actual"
                             class="w-24 h-24 rounded-xl object-cover border border-slate-200 bg-slate-50">
                        <div class="text-xs text-slate-500 pt-1">
                            <p>Imagen actual. Subí una nueva para reemplazarla.</p>
                            <form method="POST"
                                  action="{{ route('dashboard.branches.promo-image.destroy', $branch) }}"
                                  class="mt-2"
                                  onsubmit="return confirm('¿Eliminar la imagen promocional?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="text-red-500 hover:text-red-700 text-xs font-medium">
                                    <i class="fa-solid fa-trash-can mr-1"></i>Eliminar imagen
                                </button>
                            </form>
                        </div>
                    </div>
                    @endif

                    <input type="file" name="promo_image" accept="image/*"
                           class="w-full text-sm text-slate-600 border border-slate-300 rounded-lg px-3 py-2
                                  file:mr-3 file:border-0 file:bg-blue-50 file:text-blue-700
                                  file:text-xs file:font-medium file:py-1 file:px-3 file:rounded-md
                                  @error('promo_image') border-red-400 @enderror">
                    <p class="text-xs text-slate-400 mt-1">JPG, PNG o WebP. Máximo 3 MB. Recomendado: imagen comprimida (menos de 500 KB).</p>
                    @error('promo_image')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        ¿Cuándo mostrar el modal?
                    </label>
                    <div class="space-y-2">
                        @php $currentTiming = old('promo_show_when', $branch->promo_show_when); @endphp
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="promo_show_when" value=""
                                   {{ !$currentTiming ? 'checked' : '' }} class="text-blue-600">
                            <span class="text-sm text-slate-700">No mostrar (desactivado)</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="promo_show_when" value="on_load"
                                   {{ $currentTiming === 'on_load' ? 'checked' : '' }} class="text-blue-600">
                            <span class="text-sm text-slate-700">Al abrir la página de escaneo</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="promo_show_when" value="after_first_scan"
                                   {{ $currentTiming === 'after_first_scan' ? 'checked' : '' }} class="text-blue-600">
                            <span class="text-sm text-slate-700">Después del primer escaneo exitoso</span>
                        </label>
                    </div>
                    @error('promo_show_when')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    <p class="text-xs text-slate-400 mt-1">
                        Una vez cerrado, el modal no vuelve a aparecer por 24 horas.
                    </p>
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
