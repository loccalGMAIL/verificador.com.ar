@extends('layouts.app')

@section('title', 'Editar producto')
@section('page-title', 'Editar producto')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl border border-slate-200 p-6">

        <form method="POST" action="{{ route('dashboard.products.update', $product) }}"
              enctype="multipart/form-data" class="space-y-5">
            @csrf @method('PUT')

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    Nombre <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" value="{{ old('name', $product->name) }}" required
                       class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm
                              focus:outline-none focus:ring-2 focus:ring-blue-500
                              @error('name') border-red-400 @enderror">
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div x-data="barcodeGenerator()">
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    Código de barras
                </label>
                <div class="flex gap-2">
                    <div class="flex-1 relative">
                        <input type="text" name="barcode" id="barcode" x-model="barcode"
                               value="{{ old('barcode', $product->barcode) }}"
                               class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm font-mono
                                      focus:outline-none focus:ring-2 focus:ring-blue-500
                                      @error('barcode') border-red-400 @enderror">
                        {{-- Estado de verificación --}}
                        <template x-if="checking">
                            <i class="fa-solid fa-circle-notch fa-spin absolute right-3 top-3 text-slate-400 text-sm"></i>
                        </template>
                        <template x-if="!checking && barcode && status === 'available'">
                            <i class="fa-solid fa-circle-check absolute right-3 top-3 text-emerald-500 text-sm" title="Disponible"></i>
                        </template>
                        <template x-if="!checking && barcode && status === 'taken'">
                            <i class="fa-solid fa-circle-xmark absolute right-3 top-3 text-red-500 text-sm" :title="statusMessage"></i>
                        </template>
                    </div>
                    <button type="button" @click="generate()"
                            :disabled="loading"
                            class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2.5 rounded-lg text-sm font-medium transition
                                   disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2 shrink-0">
                        <i class="fa-solid fa-wand-magic-sparkles"></i>
                        <span class="hidden sm:inline">Generar</span>
                    </button>
                </div>
                <template x-if="statusMessage && status === 'taken'">
                    <p class="text-red-500 text-xs mt-1" x-text="statusMessage"></p>
                </template>
                @error('barcode')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Descripción</label>
                <textarea name="description" rows="2"
                          class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm
                                 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description', $product->description) }}</textarea>
            </div>

            {{-- Precio --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Precio</label>
                <div class="relative">
                    <span class="absolute left-3 top-2.5 text-slate-400 text-sm">$</span>
                    <input type="number" step="0.01" min="0" name="price"
                           value="{{ old('price', $product->price) }}" placeholder="0.00"
                           class="w-full border border-slate-300 rounded-lg pl-7 pr-3 py-2.5 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-blue-500
                                  @error('price') border-red-400 @enderror">
                </div>
                @error('price')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Imagen actual + nueva --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">
                    Imagen del producto <span class="text-slate-400 font-normal">(opcional)</span>
                </label>
                @if($product->image_path)
                <div class="mb-3 flex items-center gap-3">
                    <img src="{{ Storage::url($product->image_path) }}" alt=""
                         class="w-16 h-16 rounded-lg object-cover border border-slate-200">
                    <p class="text-xs text-slate-500">Imagen actual. Subí una nueva para reemplazarla.</p>
                </div>
                @endif
                <input type="file" name="image" accept="image/*"
                       class="w-full text-sm text-slate-600 border border-slate-300 rounded-lg px-3 py-2
                              file:mr-3 file:border-0 file:bg-blue-50 file:text-blue-700
                              file:text-xs file:font-medium file:py-1 file:px-3 file:rounded-md">
                <p class="text-xs text-slate-400 mt-1">JPG, PNG o WebP. Máximo 2 MB.</p>
                @error('image')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center gap-2">
                <input type="hidden" name="active" value="0">
                <input type="checkbox" name="active" id="active" value="1"
                       class="rounded border-slate-300 text-blue-600"
                       {{ old('active', $product->active) ? 'checked' : '' }}>
                <label for="active" class="text-sm text-slate-700 cursor-pointer">
                    Producto activo
                </label>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="bg-blue-600 text-white px-6 py-2.5 rounded-lg text-sm font-semibold
                               hover:bg-blue-700 transition">
                    Guardar cambios
                </button>
                <a href="{{ route('dashboard.products.index') }}"
                   class="text-slate-500 text-sm hover:text-slate-700 transition">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function barcodeGenerator() {
    return {
        barcode: '',
        loading: false,
        checking: false,
        status: '',
        statusMessage: '',
        checkTimer: null,

        async generate() {
            this.loading = true;
            try {
                const response = await fetch('{{ route('dashboard.labels.generate') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    },
                });
                const data = await response.json();
                this.barcode = data.barcode;
                await this.check();
            } catch (e) {
                console.error('Error generating barcode:', e);
            } finally {
                this.loading = false;
            }
        },

        async check() {
            if (!this.barcode.trim()) {
                this.status = '';
                this.statusMessage = '';
                return;
            }

            this.checking = true;
            try {
                const response = await fetch('{{ route('dashboard.labels.check') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ barcode: this.barcode }),
                });
                const data = await response.json();
                this.status = data.exists ? 'taken' : 'available';
                this.statusMessage = data.exists ? `En uso: ${data.product_name}` : 'Disponible';
            } catch (e) {
                console.error('Error checking barcode:', e);
                this.status = '';
            } finally {
                this.checking = false;
            }
        },

        init() {
            this.$watch('barcode', () => {
                clearTimeout(this.checkTimer);
                if (this.barcode.trim()) {
                    this.checkTimer = setTimeout(() => this.check(), 450);
                }
            });
        },
    };
}
</script>
@endpush
