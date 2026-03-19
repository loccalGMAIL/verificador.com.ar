@extends('layouts.app')

@section('title', 'Detalle de importación')
@section('page-title', 'Detalle de importación')

@section('content')
<div class="max-w-3xl space-y-6">

    {{-- Breadcrumb --}}
    <nav class="text-xs text-slate-500 flex items-center gap-1.5">
        <a href="{{ route('dashboard.products.import.index') }}" class="hover:text-slate-700">Importaciones</a>
        <span>/</span>
        <span class="text-slate-700 truncate max-w-xs">{{ basename($import->file_name) }}</span>
    </nav>

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 text-sm text-emerald-700">
        <i class="fa-solid fa-circle-check mr-2"></i>{{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-700">
        <i class="fa-solid fa-circle-xmark mr-2"></i>{{ session('error') }}
    </div>
    @endif

    {{-- Header card --}}
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <div class="flex items-start justify-between gap-4 flex-wrap">
            <div class="min-w-0">
                <p class="font-semibold text-slate-800 truncate text-base">{{ basename($import->file_name) }}</p>
                <p class="text-xs text-slate-400 mt-1">
                    {{ $import->created_at->format('d/m/Y H:i') }}
                    &bull; {{ $import->user->name ?? '—' }}
                    @if($import->importProfile)
                        &bull; Perfil: <span class="text-slate-600">{{ $import->importProfile->name }}</span>
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-3 flex-shrink-0">
                <span id="status-badge" class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium
                    {{ $import->status === 'completed'       ? 'bg-emerald-100 text-emerald-700' : '' }}
                    {{ $import->status === 'processing'      ? 'bg-blue-100 text-blue-700' : '' }}
                    {{ $import->status === 'pending'         ? 'bg-amber-100 text-amber-700' : '' }}
                    {{ $import->status === 'failed'          ? 'bg-red-100 text-red-700' : '' }}
                    {{ $import->status === 'cancelled'       ? 'bg-slate-100 text-slate-500' : '' }}">
                    @if($import->status === 'processing')
                        <i class="fa-solid fa-spinner fa-spin mr-1.5"></i>
                    @elseif($import->status === 'pending')
                        <i class="fa-solid fa-clock mr-1.5"></i>
                    @elseif($import->status === 'completed')
                        <i class="fa-solid fa-circle-check mr-1.5"></i>
                    @elseif($import->status === 'failed')
                        <i class="fa-solid fa-circle-xmark mr-1.5"></i>
                    @elseif($import->status === 'cancelled')
                        <i class="fa-solid fa-ban mr-1.5"></i>
                    @endif
                    <span id="status-text">{{ match($import->status) {
                        'completed'  => 'Completado',
                        'processing' => 'Procesando...',
                        'pending'    => 'En cola',
                        'failed'     => 'Fallido',
                        'cancelled'  => 'Cancelado',
                        default      => $import->status
                    } }}</span>
                </span>

                @if($import->status === 'pending')
                <form id="cancel-form" method="POST" action="{{ route('dashboard.products.import.cancel', $import) }}"
                      onsubmit="return confirm('¿Cancelar esta importación?')">
                    @csrf
                    <button type="submit"
                            class="bg-red-50 text-red-600 hover:bg-red-100 border border-red-200
                                   px-4 py-1.5 rounded-lg text-sm font-medium transition">
                        <i class="fa-solid fa-ban mr-1.5"></i>Cancelar importación
                    </button>
                </form>
                @endif
            </div>
        </div>

        {{-- Barra de progreso (visible solo cuando está procesando) --}}
        @if(in_array($import->status, ['pending', 'processing']))
        <div id="progress-section" class="mt-5">
            <div class="flex justify-between items-center mb-1.5">
                <p id="progress-label" class="text-xs text-slate-500 flex items-center gap-1.5">
                    <i class="fa-solid fa-rotate fa-spin"></i>
                    <span id="progress-text">Iniciando procesamiento...</span>
                </p>
                <span id="progress-pct" class="text-xs font-semibold text-slate-700">0%</span>
            </div>
            <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                <div id="progress-bar"
                     class="h-full bg-blue-500 rounded-full transition-all duration-500"
                     style="width: 0%"></div>
            </div>
            <div id="progress-counters" class="mt-2 flex gap-4 text-xs text-slate-500 hidden">
                <span><span id="cnt-processed" class="font-semibold text-slate-700">0</span> / <span id="cnt-total" class="font-semibold text-slate-700">0</span> filas</span>
                <span class="text-emerald-600"><i class="fa-solid fa-check mr-0.5"></i><span id="cnt-ok">0</span> OK</span>
                <span class="text-red-500"><i class="fa-solid fa-xmark mr-0.5"></i><span id="cnt-errors">0</span> errores</span>
            </div>
        </div>
        @endif
    </div>

    {{-- Stats --}}
    @if($import->rows_total > 0 || $import->status === 'completed')
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-slate-200 p-4 text-center">
            <p class="text-2xl font-bold text-slate-800">{{ $import->rows_total }}</p>
            <p class="text-xs text-slate-500 mt-0.5">Total de filas</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 text-center">
            <p class="text-2xl font-bold text-emerald-600">{{ $import->rows_ok }}</p>
            <p class="text-xs text-slate-500 mt-0.5">Importadas OK</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 text-center">
            <p class="text-2xl font-bold text-red-500">{{ $import->rows_error }}</p>
            <p class="text-xs text-slate-500 mt-0.5">Con error</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 text-center">
            @php
                $pct = $import->rows_total > 0
                    ? round(($import->rows_ok / $import->rows_total) * 100)
                    : 0;
            @endphp
            <p class="text-2xl font-bold {{ $pct === 100 ? 'text-emerald-600' : 'text-amber-600' }}">{{ $pct }}%</p>
            <p class="text-xs text-slate-500 mt-0.5">Éxito</p>
        </div>
    </div>
    @endif

    {{-- Error log --}}
    @if($import->error_log && count($import->error_log) > 0)
    <div class="bg-white rounded-xl border border-red-200 overflow-hidden">
        <div class="px-5 py-3 bg-red-50 border-b border-red-200 flex items-center gap-2">
            <i class="fa-solid fa-triangle-exclamation text-red-500"></i>
            <h3 class="text-sm font-semibold text-red-700">
                {{ count($import->error_log) }} error(es) encontrados
            </h3>
        </div>
        <ul class="divide-y divide-slate-50 max-h-96 overflow-y-auto">
            @foreach($import->error_log as $err)
            <li class="px-5 py-2.5 flex items-start gap-3 text-sm">
                @if(($err['row'] ?? 0) > 0)
                <span class="flex-shrink-0 bg-red-100 text-red-600 text-xs font-mono px-2 py-0.5 rounded">
                    Fila {{ $err['row'] }}
                </span>
                @else
                <span class="flex-shrink-0 bg-slate-100 text-slate-600 text-xs font-mono px-2 py-0.5 rounded">
                    General
                </span>
                @endif
                <span class="text-slate-700">{{ $err['error'] }}</span>
            </li>
            @endforeach
        </ul>
    </div>
    @endif

    <a href="{{ route('dashboard.products.import.index') }}"
       class="inline-block text-slate-500 text-sm hover:text-slate-700">
        ← Volver a importaciones
    </a>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const status = @json($import->status);

    if (status !== 'pending') return;

    const processUrl  = '{{ route('dashboard.products.import.process',  $import) }}';
    const progressUrl = '{{ route('dashboard.products.import.progress', $import) }}';
    const csrfToken   = '{{ csrf_token() }}';

    // Disparar el procesamiento (request larga, no esperamos respuesta aquí)
    fetch(processUrl, {
        method:  'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
    }).catch(() => {}); // ignorar errores de red — el polling lo detectará

    // Elementos del DOM
    const bar        = document.getElementById('progress-bar');
    const pctLabel   = document.getElementById('progress-pct');
    const textLabel  = document.getElementById('progress-text');
    const counters   = document.getElementById('progress-counters');
    const cntProcessed = document.getElementById('cnt-processed');
    const cntTotal     = document.getElementById('cnt-total');
    const cntOk        = document.getElementById('cnt-ok');
    const cntErrors    = document.getElementById('cnt-errors');

    // Polling cada 2 segundos
    const interval = setInterval(async () => {
        try {
            const res  = await fetch(progressUrl, { headers: { 'Accept': 'application/json' } });
            const data = await res.json();

            // Completado: recargar primero, sin tocar DOM
            if (data.is_complete) {
                clearInterval(interval);
                setTimeout(() => location.reload(), 800);
                return;
            }

            // Actualizar barra
            const pct = data.percentage ?? 0;
            if (bar)      bar.style.width      = pct + '%';
            if (pctLabel) pctLabel.textContent = pct + '%';

            // Actualizar contadores si ya hay datos
            if (data.total > 0) {
                if (counters)      counters.classList.remove('hidden');
                if (cntProcessed)  cntProcessed.textContent = data.processed;
                if (cntTotal)      cntTotal.textContent     = data.total;
                if (cntOk)         cntOk.textContent        = data.ok;
                if (cntErrors)     cntErrors.textContent    = data.errors;
                if (textLabel)     textLabel.textContent    = 'Procesando filas...';
            }
        } catch (_) {
            // ignorar errores transitorios de red
        }
    }, 2000);
})();
</script>
@endpush
