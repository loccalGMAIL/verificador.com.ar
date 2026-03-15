@extends('layouts.app')

@section('title', 'Importar productos')
@section('page-title', 'Importar productos')

@section('content')
<div class="max-w-2xl space-y-6">

    {{-- Formulario de carga --}}
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <h3 class="font-semibold text-slate-800 mb-1">Subir archivo</h3>
        <p class="text-sm text-slate-500 mb-5">
            Aceptamos archivos <strong>.xlsx</strong>, <strong>.xls</strong> y <strong>.csv</strong>
            con las columnas del formato esperado.
            <a href="{{ route('dashboard.products.import.template') }}"
               class="text-blue-600 hover:underline font-medium">
                Descargar plantilla CSV
            </a>
        </p>

        {{-- Columnas esperadas --}}
        <div class="mb-5 bg-slate-50 rounded-lg p-4 text-xs text-slate-600 font-mono space-y-1 border border-slate-200">
            <p class="font-sans font-semibold text-slate-700 mb-2 text-xs tracking-wide uppercase">Columnas requeridas</p>
            <p><span class="text-blue-600">codigo_barras</span> — requerido</p>
            <p><span class="text-blue-600">nombre</span> — requerido</p>
            <p><span class="text-slate-400">descripcion</span> — opcional</p>
            <p><span class="text-slate-400">precio_ars</span> — al menos uno de los dos precios</p>
            <p><span class="text-slate-400">precio_usd</span> — al menos uno de los dos precios</p>
            <p><span class="text-slate-400">moneda_default</span> — ARS o USD (default: ARS)</p>
        </div>

        <form method="POST" action="{{ route('dashboard.products.import.store') }}"
              enctype="multipart/form-data">
            @csrf
            <div class="mb-4">
                <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                       class="w-full text-sm text-slate-600 border border-slate-300 rounded-lg px-3 py-3
                              file:mr-3 file:border-0 file:bg-blue-50 file:text-blue-700
                              file:text-xs file:font-medium file:py-1.5 file:px-4 file:rounded-md
                              @error('file') border-red-400 @enderror">
                <p class="text-xs text-slate-400 mt-1">Tamaño máximo: 10 MB</p>
                @error('file')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <button type="submit"
                    class="bg-blue-600 text-white px-6 py-2.5 rounded-lg text-sm font-semibold
                           hover:bg-blue-700 transition flex items-center gap-2">
                <i class="fa-solid fa-upload"></i>
                Subir e importar
            </button>
        </form>
    </div>

    {{-- Historial de importaciones --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h3 class="font-semibold text-slate-800">Historial de importaciones</h3>
        </div>

        @forelse($imports as $import)
        <div class="px-5 py-4 border-b border-slate-50 last:border-0">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-slate-800 truncate">
                        {{ basename($import->file_name) }}
                    </p>
                    <p class="text-xs text-slate-400 mt-0.5">
                        {{ $import->created_at->format('d/m/Y H:i') }}
                        &bull; {{ $import->user->name ?? '—' }}
                    </p>
                    @if($import->isCompleted() && $import->rows_total > 0)
                    <p class="text-xs text-slate-500 mt-1">
                        {{ $import->rows_ok }} ok
                        @if($import->rows_error > 0)
                            · <span class="text-red-500">{{ $import->rows_error }} con error</span>
                        @endif
                        / {{ $import->rows_total }} filas
                    </p>
                    @endif
                </div>
                <span class="flex-shrink-0 inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                    {{ $import->status === 'completed' ? 'bg-emerald-100 text-emerald-700' : '' }}
                    {{ $import->status === 'processing' ? 'bg-blue-100 text-blue-700' : '' }}
                    {{ $import->status === 'pending' ? 'bg-amber-100 text-amber-700' : '' }}
                    {{ $import->status === 'failed' ? 'bg-red-100 text-red-700' : '' }}">
                    {{ match($import->status) {
                        'completed'  => 'Completado',
                        'processing' => 'Procesando...',
                        'pending'    => 'En cola',
                        'failed'     => 'Fallido',
                        default      => $import->status
                    } }}
                </span>
            </div>

            {{-- Errores del import --}}
            @if($import->error_log && count($import->error_log) > 0)
            <details class="mt-2">
                <summary class="text-xs text-red-500 cursor-pointer hover:text-red-700 font-medium">
                    Ver {{ count($import->error_log) }} error(es)
                </summary>
                <ul class="mt-2 space-y-0.5 max-h-32 overflow-y-auto">
                    @foreach($import->error_log as $err)
                    <li class="text-xs text-red-600 bg-red-50 px-3 py-1 rounded">
                        {{ $err['error'] }}
                    </li>
                    @endforeach
                </ul>
            </details>
            @endif
        </div>
        @empty
        <div class="px-5 py-10 text-center text-slate-400 text-sm">
            Todavía no realizaste ninguna importación.
        </div>
        @endforelse

        @if($imports->hasPages())
        <div class="px-5 py-3 border-t border-slate-100">
            {{ $imports->links() }}
        </div>
        @endif
    </div>

    <a href="{{ route('dashboard.products.index') }}"
       class="inline-block text-slate-500 text-sm hover:text-slate-700">
        ← Volver a productos
    </a>
</div>
@endsection
