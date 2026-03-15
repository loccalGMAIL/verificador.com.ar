@extends('layouts.app')

@section('title', 'Configurar impresión QR – ' . $branch->name)
@section('page-title', 'Configurar impresión QR')

@section('content')

{{-- Cabecera con nombre de sucursal --}}
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('dashboard.branches.index') }}"
       class="text-slate-400 hover:text-slate-700 transition">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <div>
        <h2 class="text-base font-semibold text-slate-800">{{ $branch->name }}</h2>
        <p class="text-xs text-slate-400">Personalizá el cartel antes de imprimir</p>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

    {{-- ══ COLUMNA IZQUIERDA: Formulario ══ --}}
    <div class="space-y-4">

        {{-- Esquema de color --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Color del encabezado</h3>
            <div class="flex items-center gap-3 flex-wrap">
                @php
                    $schemes = [
                        'blue'   => ['label' => 'Azul',    'from' => '#1e3a8a', 'to' => '#1d4ed8'],
                        'green'  => ['label' => 'Verde',   'from' => '#065f46', 'to' => '#059669'],
                        'dark'   => ['label' => 'Oscuro',  'from' => '#111827', 'to' => '#374151'],
                        'purple' => ['label' => 'Violeta', 'from' => '#4c1d95', 'to' => '#7c3aed'],
                        'orange' => ['label' => 'Naranja', 'from' => '#7c2d12', 'to' => '#ea580c'],
                    ];
                @endphp

                @foreach($schemes as $key => $s)
                <label class="scheme-option cursor-pointer group" title="{{ $s['label'] }}">
                    <input type="radio" name="scheme_radio" value="{{ $key }}"
                           class="sr-only"
                           {{ $key === 'blue' ? 'checked' : '' }}
                           onchange="schedulePreviewUpdate()">
                    <div class="w-10 h-10 rounded-lg border-2 border-transparent group-hover:border-slate-400 transition scheme-swatch
                                {{ $key === 'blue' ? 'ring-2 ring-offset-2 ring-slate-500 !border-slate-500' : '' }}"
                         data-scheme="{{ $key }}"
                         style="background: linear-gradient(135deg, {{ $s['from'] }}, {{ $s['to'] }})">
                    </div>
                    <p class="text-xs text-slate-500 text-center mt-1">{{ $s['label'] }}</p>
                </label>
                @endforeach
            </div>
        </div>

        {{-- Texto del cartel --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Texto del cartel</h3>
            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Título principal</label>
                    <input type="text" id="input-headline"
                           value="Verificá tu precio"
                           maxlength="40"
                           oninput="schedulePreviewUpdate()"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <p class="text-xs text-slate-400 mt-1">Máx. 40 caracteres</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Instrucción</label>
                    <textarea id="input-instruction" rows="2" maxlength="120"
                              oninput="schedulePreviewUpdate()"
                              class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-400 resize-none">Escaneá el código con tu celular
para verificar el precio al instante</textarea>
                    <p class="text-xs text-slate-400 mt-1">Máx. 120 caracteres</p>
                </div>
            </div>
        </div>

        {{-- Opciones de visibilidad --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Visibilidad</h3>
            <div class="space-y-3">
                <label class="flex items-center justify-between cursor-pointer">
                    <span class="text-sm text-slate-700">Mostrar logo del comercio</span>
                    <div class="relative">
                        <input type="checkbox" id="toggle-logo" class="sr-only" checked onchange="schedulePreviewUpdate()">
                        <div class="toggle-track w-11 h-6 bg-slate-200 rounded-full transition"></div>
                        <div class="toggle-dot absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow transition"></div>
                    </div>
                </label>
                <label class="flex items-center justify-between cursor-pointer">
                    <span class="text-sm text-slate-700">Mostrar nombre de sucursal</span>
                    <div class="relative">
                        <input type="checkbox" id="toggle-branch" class="sr-only" checked onchange="schedulePreviewUpdate()">
                        <div class="toggle-track w-11 h-6 bg-slate-200 rounded-full transition"></div>
                        <div class="toggle-dot absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow transition"></div>
                    </div>
                </label>
            </div>
        </div>

        {{-- Botón Imprimir --}}
        <button id="btn-print"
                class="w-full flex items-center justify-center gap-2 bg-emerald-600 text-white font-semibold
                       py-3 rounded-xl hover:bg-emerald-700 active:scale-95 transition text-sm shadow-sm">
            <i class="fa-solid fa-print"></i>
            Imprimir QR
        </button>
    </div>

    {{-- ══ COLUMNA DERECHA: Preview via iframe ══ --}}
    <div class="flex flex-col items-center">
        <p class="text-xs font-medium text-slate-500 mb-3 uppercase tracking-wide">Vista previa</p>

        {{--
            El iframe carga exactamente la misma vista de impresión con ?preview=1
            (desactiva auto-print). Se escala con CSS transform para entrar en el panel.

            Dimensiones reales de la hoja A5 apaisado a 96dpi:
              210mm × 148mm → 794px × 559px
            Factor de escala para 480px de ancho de contenedor:
              480 / 794 ≈ 0.604  →  alto resultante: 559 × 0.604 ≈ 337px
        --}}
        <div class="rounded-xl shadow-lg border border-slate-200 overflow-hidden bg-white"
             style="width: 480px; height: 338px; position: relative;">

            {{-- Overlay de carga --}}
            <div id="preview-loading"
                 class="absolute inset-0 bg-white/80 flex items-center justify-center z-10 transition-opacity"
                 style="display: none !important;">
                <svg class="animate-spin h-6 w-6 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                </svg>
            </div>

            <iframe id="preview-iframe"
                    src="{{ route('dashboard.branches.qr', $branch) }}?preview=1"
                    width="794"
                    height="559"
                    scrolling="no"
                    style="border: none; display: block;
                           transform: scale(0.604);
                           transform-origin: top left;"
                    onload="document.getElementById('preview-loading').style.display = 'none'">
            </iframe>
        </div>

        <p class="text-xs text-slate-400 mt-3">
            <i class="fa-solid fa-scissors mr-1"></i>
            Se imprimirán 2 carteles por hoja A5 apaisado
        </p>
    </div>

</div>

@endsection

@push('styles')
<style>
    /* Toggle switch */
    input:checked + .toggle-track { background-color: #10b981; }
    input:checked ~ .toggle-dot   { transform: translateX(20px); }
    .toggle-track, .toggle-dot    { transition: all .2s; }
</style>
@endpush

@push('scripts')
<script>
(function () {
    const BASE_URL   = "{{ route('dashboard.branches.qr', $branch) }}";
    const iframe     = document.getElementById('preview-iframe');
    const btnPrint   = document.getElementById('btn-print');
    let   debounce;

    // ── Construir parámetros actuales ────────────────────────────────
    function buildParams(isPreview) {
        const scheme = document.querySelector('.scheme-option input:checked')?.value ?? 'blue';
        return new URLSearchParams({
            scheme,
            headline:    document.getElementById('input-headline').value    || 'Verificá tu precio',
            instruction: document.getElementById('input-instruction').value || '',
            show_logo:   document.getElementById('toggle-logo').checked    ? '1' : '0',
            show_branch: document.getElementById('toggle-branch').checked  ? '1' : '0',
            ...(isPreview ? { preview: '1' } : {}),
        });
    }

    // ── Actualizar iframe (debounced) ────────────────────────────────
    window.schedulePreviewUpdate = function () {
        clearTimeout(debounce);
        debounce = setTimeout(function () {
            // Actualizar anillo de selección del swatch
            const selected = document.querySelector('.scheme-option input:checked')?.value;
            document.querySelectorAll('.scheme-swatch').forEach(el => {
                const isActive = el.dataset.scheme === selected;
                el.classList.toggle('ring-2',         isActive);
                el.classList.toggle('ring-offset-2',  isActive);
                el.classList.toggle('ring-slate-500', isActive);
                el.classList.toggle('!border-slate-500', isActive);
            });

            // _t evita que el navegador cachee la respuesta anterior
            iframe.src = BASE_URL + '?' + buildParams(true).toString() + '&_t=' + Date.now();
        }, 350);
    };

    // ── Botón imprimir ───────────────────────────────────────────────
    btnPrint.addEventListener('click', function () {
        window.open(BASE_URL + '?' + buildParams(false).toString(), '_blank');
    });

})();
</script>
@endpush
