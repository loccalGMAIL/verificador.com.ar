{{--
    Partial: formulario de configuración + preview del QR.
    Requiere: $branch (con store cargado)
--}}

@php
    $def_instruction = "Escaneá el código con tu celular\npara verificar el precio al instante";

    $saved = [
        'scheme'        => old('qr_scheme',        $branch->qr_scheme        ?? 'blue'),
        'layout'        => old('qr_layout',        $branch->qr_layout        ?? 'a5'),
        'headline'      => old('qr_headline',      $branch->qr_headline      ?? 'Verificá tu precio'),
        'instruction'   => old('qr_instruction',   $branch->qr_instruction   ?? $def_instruction),
        'show_logo'     => old('qr_show_logo',     $branch->qr_show_logo     ?? true),
        'show_branch'   => old('qr_show_branch',   $branch->qr_show_branch   ?? true),
        'logo_position' => old('qr_logo_position', $branch->qr_logo_position ?? 'center'),
        'qr_size'       => old('qr_qr_size',       $branch->qr_qr_size       ?? 'md'),
        'headline_size' => old('qr_headline_size', $branch->qr_headline_size ?? 'md'),
        'instr_size'    => old('qr_instr_size',    $branch->qr_instr_size    ?? 'md'),
        'logo_size'     => old('qr_logo_size',     $branch->qr_logo_size     ?? 'md'),
    ];

    $schemes = [
        'blue'   => ['label' => 'Azul',      'from' => '#1e3a8a', 'to' => '#1d4ed8'],
        'green'  => ['label' => 'Verde',     'from' => '#065f46', 'to' => '#059669'],
        'dark'   => ['label' => 'Oscuro',    'from' => '#111827', 'to' => '#374151'],
        'purple' => ['label' => 'Violeta',   'from' => '#4c1d95', 'to' => '#7c3aed'],
        'orange' => ['label' => 'Naranja',   'from' => '#7c2d12', 'to' => '#ea580c'],
        'red'    => ['label' => 'Rojo',      'from' => '#991b1b', 'to' => '#dc2626'],
        'sky'    => ['label' => 'Celeste',   'from' => '#0c4a6e', 'to' => '#0284c7'],
        'pink'   => ['label' => 'Rosa',      'from' => '#831843', 'to' => '#db2777'],
        'teal'   => ['label' => 'Turquesa',  'from' => '#134e4a', 'to' => '#0d9488'],
        'amber'  => ['label' => 'Dorado',    'from' => '#92400e', 'to' => '#d97706'],
    ];

    $formId    = 'qr-form-' . $branch->id;
    $iframeId  = 'preview-iframe-' . $branch->id;
    $wrapId    = 'preview-wrap-' . $branch->id;
    $hintId    = 'layout-hint-' . $branch->id;
    $btnPrintId= 'btn-print-' . $branch->id;
@endphp

<div class="grid grid-cols-1 xl:grid-cols-2 gap-6 items-start">

    {{-- ══ COLUMNA IZQUIERDA: Formulario ══ --}}
    <form id="{{ $formId }}" method="POST" action="{{ route('dashboard.branches.qr.save', $branch) }}"
          x-data="{ open: null }">
        @csrf

        <div class="space-y-2">

            {{-- ── Color ──────────────────────────────────────────── --}}
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                <button type="button" @click="open = open === 'color' ? null : 'color'"
                        class="w-full flex items-center justify-between px-5 py-3.5 text-left">
                    <span class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                        <i class="fa-solid fa-palette w-4 text-center text-slate-400"></i>
                        Color del encabezado
                        <span class="text-xs font-normal text-slate-400">· {{ $schemes[$saved['scheme']]['label'] ?? '' }}</span>
                    </span>
                    <i class="fa-solid fa-chevron-down text-xs text-slate-400 transition-transform duration-200"
                       :class="open === 'color' ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="open === 'color'" x-transition class="px-5 pb-5 border-t border-slate-100">
                    <div class="grid grid-cols-5 gap-2 pt-4">
                        @foreach($schemes as $key => $s)
                        <label class="scheme-option cursor-pointer group" title="{{ $s['label'] }}">
                            <input type="radio" name="qr_scheme" value="{{ $key }}"
                                   class="sr-only"
                                   {{ $key === $saved['scheme'] ? 'checked' : '' }}
                                   onchange="schedulePreviewUpdate_{{ $branch->id }}()">
                            <div class="w-full aspect-square rounded-lg border-2 border-transparent
                                        group-hover:border-slate-400 transition scheme-swatch-{{ $branch->id }}
                                        {{ $key === $saved['scheme'] ? 'ring-2 ring-offset-2 ring-slate-500 !border-slate-500' : '' }}"
                                 data-scheme="{{ $key }}"
                                 style="background: linear-gradient(135deg, {{ $s['from'] }}, {{ $s['to'] }})">
                            </div>
                            <p class="text-[10px] text-slate-500 text-center mt-1 leading-tight">{{ $s['label'] }}</p>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ── Texto ───────────────────────────────────────────── --}}
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                <button type="button" @click="open = open === 'texto' ? null : 'texto'"
                        class="w-full flex items-center justify-between px-5 py-3.5 text-left">
                    <span class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                        <i class="fa-solid fa-pen-to-square w-4 text-center text-slate-400"></i>
                        Texto del cartel
                    </span>
                    <i class="fa-solid fa-chevron-down text-xs text-slate-400 transition-transform duration-200"
                       :class="open === 'texto' ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="open === 'texto'" x-transition class="px-5 pb-5 border-t border-slate-100">
                    <div class="space-y-3 pt-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Título principal</label>
                            <input type="text" id="input-headline-{{ $branch->id }}" name="qr_headline"
                                   value="{{ $saved['headline'] }}"
                                   maxlength="80"
                                   oninput="schedulePreviewUpdate_{{ $branch->id }}()"
                                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-400">
                            <p class="text-xs text-slate-400 mt-1">Máx. 80 caracteres</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Instrucción</label>
                            <textarea id="input-instruction-{{ $branch->id }}" name="qr_instruction" rows="2" maxlength="200"
                                      oninput="schedulePreviewUpdate_{{ $branch->id }}()"
                                      class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-400 resize-none">{{ $saved['instruction'] }}</textarea>
                            <p class="text-xs text-slate-400 mt-1">Máx. 200 caracteres</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Visibilidad ─────────────────────────────────────── --}}
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                <button type="button" @click="open = open === 'visibilidad' ? null : 'visibilidad'"
                        class="w-full flex items-center justify-between px-5 py-3.5 text-left">
                    <span class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                        <i class="fa-solid fa-eye w-4 text-center text-slate-400"></i>
                        Visibilidad y posición del logo
                    </span>
                    <i class="fa-solid fa-chevron-down text-xs text-slate-400 transition-transform duration-200"
                       :class="open === 'visibilidad' ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="open === 'visibilidad'" x-transition class="px-5 pb-5 border-t border-slate-100">
                    <div class="space-y-4 pt-4">

                        <label class="flex items-center justify-between cursor-pointer">
                            <span class="text-sm text-slate-700">Mostrar logo del comercio</span>
                            <div class="relative">
                                <input type="checkbox" id="toggle-logo-{{ $branch->id }}" name="qr_show_logo" value="1"
                                       class="sr-only"
                                       {{ $saved['show_logo'] ? 'checked' : '' }}
                                       onchange="schedulePreviewUpdate_{{ $branch->id }}()">
                                <div class="toggle-track w-11 h-6 bg-slate-200 rounded-full transition"></div>
                                <div class="toggle-dot absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow transition"></div>
                            </div>
                        </label>

                        <div>
                            <p class="text-xs font-medium text-slate-600 mb-2">Posición del logo</p>
                            <div class="flex gap-1">
                                @foreach(['left' => ['label' => 'Izquierda', 'icon' => 'fa-align-left'],
                                          'center' => ['label' => 'Centro', 'icon' => 'fa-align-center'],
                                          'right'  => ['label' => 'Derecha', 'icon' => 'fa-align-right']]
                                         as $pos => $meta)
                                <label class="flex-1 cursor-pointer relative">
                                    <input type="radio" name="qr_logo_position" value="{{ $pos }}" class="sr-only"
                                           {{ $saved['logo_position'] === $pos ? 'checked' : '' }}
                                           onchange="schedulePreviewUpdate_{{ $branch->id }}()">
                                    <div class="size-pill border border-slate-200 rounded-lg py-2 text-center text-xs font-medium text-slate-500
                                                hover:border-blue-400 hover:text-blue-600 transition select-none flex flex-col items-center gap-0.5">
                                        <i class="fa-solid {{ $meta['icon'] }} text-sm"></i>
                                        <span>{{ $meta['label'] }}</span>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <p class="text-xs font-medium text-slate-600 mb-2">Tamaño del logo</p>
                            <div class="flex gap-1">
                                @foreach(['sm' => 'Pequeño', 'md' => 'Normal', 'lg' => 'Grande'] as $val => $lbl)
                                <label class="flex-1 cursor-pointer relative">
                                    <input type="radio" name="qr_logo_size" value="{{ $val }}" class="sr-only"
                                           {{ $saved['logo_size'] === $val ? 'checked' : '' }}
                                           onchange="schedulePreviewUpdate_{{ $branch->id }}()">
                                    <div class="size-pill border border-slate-200 rounded-lg py-1.5 text-center text-xs font-medium
                                                text-slate-500 hover:border-blue-400 hover:text-blue-600 transition select-none">
                                        {{ $lbl }}
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        <label class="flex items-center justify-between cursor-pointer">
                            <span class="text-sm text-slate-700">Mostrar nombre de sucursal</span>
                            <div class="relative">
                                <input type="checkbox" id="toggle-branch-{{ $branch->id }}" name="qr_show_branch" value="1"
                                       class="sr-only"
                                       {{ $saved['show_branch'] ? 'checked' : '' }}
                                       onchange="schedulePreviewUpdate_{{ $branch->id }}()">
                                <div class="toggle-track w-11 h-6 bg-slate-200 rounded-full transition"></div>
                                <div class="toggle-dot absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow transition"></div>
                            </div>
                        </label>

                    </div>
                </div>
            </div>

            {{-- ── Diseño ──────────────────────────────────────────── --}}
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                <button type="button" @click="open = open === 'diseno' ? null : 'diseno'"
                        class="w-full flex items-center justify-between px-5 py-3.5 text-left">
                    <span class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                        <i class="fa-solid fa-ruler-combined w-4 text-center text-slate-400"></i>
                        Diseño e impresión
                    </span>
                    <i class="fa-solid fa-chevron-down text-xs text-slate-400 transition-transform duration-200"
                       :class="open === 'diseno' ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="open === 'diseno'" x-transition class="px-5 pb-5 border-t border-slate-100">
                    <div class="pt-4 space-y-4">

                        <div>
                            <p class="text-xs font-medium text-slate-600 mb-2">Formato de hoja</p>
                            <div class="grid grid-cols-2 gap-2">
                                <label class="layout-option cursor-pointer">
                                    <input type="radio" name="qr_layout" value="a5" class="sr-only"
                                           {{ $saved['layout'] === 'a5' ? 'checked' : '' }}
                                           onchange="onLayoutChange_{{ $branch->id }}('a5'); schedulePreviewUpdate_{{ $branch->id }}()">
                                    <div class="layout-card border-2 border-slate-200 bg-slate-50 rounded-lg p-3 text-center transition">
                                        <div class="flex justify-center gap-1 mb-1.5">
                                            <div class="layout-icon-block w-8 h-6 bg-slate-400 rounded-sm"></div>
                                            <div class="layout-icon-block w-8 h-6 bg-slate-400 rounded-sm"></div>
                                        </div>
                                        <p class="layout-label text-xs font-semibold text-slate-600">2 copias · A5</p>
                                        <p class="text-[10px] text-slate-400 mt-0.5">Apaisado</p>
                                    </div>
                                </label>
                                <label class="layout-option cursor-pointer">
                                    <input type="radio" name="qr_layout" value="a4" class="sr-only"
                                           {{ $saved['layout'] === 'a4' ? 'checked' : '' }}
                                           onchange="onLayoutChange_{{ $branch->id }}('a4'); schedulePreviewUpdate_{{ $branch->id }}()">
                                    <div class="layout-card border-2 border-slate-200 bg-slate-50 rounded-lg p-3 text-center transition">
                                        <div class="flex justify-center mb-1.5">
                                            <div class="layout-icon-block w-8 h-10 bg-slate-400 rounded-sm"></div>
                                        </div>
                                        <p class="layout-label text-xs font-semibold text-slate-600">1 copia · A4</p>
                                        <p class="text-[10px] text-slate-400 mt-0.5">Vertical</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        @php
                            $sizeGroups = [
                                ['name' => 'qr_qr_size',       'label' => 'Tamaño del QR',
                                 'opts' => ['sm' => 'Peq.', 'md' => 'Normal', 'lg' => 'Grande', 'xl' => 'Muy gde.']],
                                ['name' => 'qr_headline_size',  'label' => 'Tamaño del título',
                                 'opts' => ['sm' => 'Pequeño', 'md' => 'Normal', 'lg' => 'Grande']],
                                ['name' => 'qr_instr_size',     'label' => 'Tamaño de instrucción',
                                 'opts' => ['sm' => 'Pequeño', 'md' => 'Normal', 'lg' => 'Grande']],
                            ];
                            $sizeDefaults = [
                                'qr_qr_size'       => $saved['qr_size'],
                                'qr_headline_size' => $saved['headline_size'],
                                'qr_instr_size'    => $saved['instr_size'],
                            ];
                        @endphp

                        @foreach($sizeGroups as $group)
                        <div>
                            <p class="text-xs font-medium text-slate-600 mb-2">{{ $group['label'] }}</p>
                            <div class="flex gap-1">
                                @foreach($group['opts'] as $val => $lbl)
                                <label class="flex-1 cursor-pointer relative">
                                    <input type="radio" name="{{ $group['name'] }}" value="{{ $val }}"
                                           class="sr-only"
                                           {{ $sizeDefaults[$group['name']] === $val ? 'checked' : '' }}
                                           onchange="schedulePreviewUpdate_{{ $branch->id }}()">
                                    <div class="size-pill border border-slate-200 rounded-lg py-1.5 text-center text-xs font-medium
                                                text-slate-500 hover:border-blue-400 hover:text-blue-600 transition select-none">
                                        {{ $lbl }}
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endforeach

                    </div>
                </div>
            </div>

            {{-- Botones --}}
            <div class="flex gap-3 pt-1">
                <button type="submit"
                        class="flex-1 flex items-center justify-center gap-2 bg-blue-600 text-white font-semibold
                               py-3 rounded-xl hover:bg-blue-700 active:scale-95 transition text-sm">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Guardar configuración
                </button>
                <button type="button" id="{{ $btnPrintId }}"
                        class="flex-1 flex items-center justify-center gap-2 bg-emerald-600 text-white font-semibold
                               py-3 rounded-xl hover:bg-emerald-700 active:scale-95 transition text-sm">
                    <i class="fa-solid fa-print"></i>
                    Imprimir QR
                </button>
            </div>

        </div>
    </form>

    {{-- ══ COLUMNA DERECHA: Preview ══ --}}
    <div class="flex flex-col items-center xl:sticky xl:top-4">
        <p class="text-xs font-medium text-slate-500 mb-3 uppercase tracking-wide">Vista previa</p>

        <div id="{{ $wrapId }}"
             class="rounded-xl shadow-lg border border-slate-200 bg-white transition-all duration-300"
             style="width: 480px; height: 338px; position: relative; overflow: hidden; flex-shrink: 0;">

            <div class="absolute inset-0 bg-white/80 flex items-center justify-center z-10"
                 style="display: none !important;">
                <svg class="animate-spin h-6 w-6 text-slate-400" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                </svg>
            </div>

            <iframe id="{{ $iframeId }}"
                    src="{{ route('dashboard.branches.qr', $branch) }}?{{ http_build_query([
                        'preview'       => '1',
                        'scheme'        => $saved['scheme'],
                        'layout'        => $saved['layout'],
                        'headline'      => $saved['headline'],
                        'instruction'   => $saved['instruction'],
                        'show_logo'     => $saved['show_logo']   ? '1' : '0',
                        'show_branch'   => $saved['show_branch'] ? '1' : '0',
                        'logo_position' => $saved['logo_position'],
                        'qr_size'       => $saved['qr_size'],
                        'headline_size' => $saved['headline_size'],
                        'instr_size'    => $saved['instr_size'],
                        'logo_size'     => $saved['logo_size'],
                    ]) }}"
                    scrolling="no"
                    style="position: absolute; top: 0; left: 0;
                           width: 794px; height: 559px; border: none;
                           transform: scale(0.6045); transform-origin: top left;">
            </iframe>
        </div>

        <p id="{{ $hintId }}" class="text-xs text-slate-400 mt-3">
            <i class="fa-solid fa-scissors mr-1"></i>
            {{ $saved['layout'] === 'a4' ? '1 cartel por hoja A4 vertical' : '2 carteles por hoja A5 apaisado' }}
        </p>
    </div>

</div>

@push('styles')
<style>
    input:checked + .toggle-track { background-color: #10b981; }
    input:checked ~ .toggle-dot   { transform: translateX(20px); }
    .toggle-track, .toggle-dot    { transition: all .2s; }

    input[type="radio"]:checked + .size-pill {
        border-color: #3b82f6 !important;
        color: #2563eb !important;
        background-color: #eff6ff;
    }
    input[type="radio"]:checked + .layout-card {
        border-color: #3b82f6 !important;
        background-color: #eff6ff !important;
    }
    input[type="radio"]:checked + .layout-card .layout-label   { color: #1d4ed8 !important; }
    input[type="radio"]:checked + .layout-card .layout-icon-block { background-color: #3b82f6 !important; }
</style>
@endpush

@push('scripts')
<script>
(function () {
    const BASE_URL = "{{ route('dashboard.branches.qr', $branch) }}";
    const iframe   = document.getElementById('{{ $iframeId }}');
    const btnPrint = document.getElementById('{{ $btnPrintId }}');
    const wrap     = document.getElementById('{{ $wrapId }}');
    const hint     = document.getElementById('{{ $hintId }}');
    const formEl   = document.getElementById('{{ $formId }}');
    let   debounce;

    const SCALE = 0.6045;
    const LAYOUTS = {
        a5: { iframeH: 559,  containerH: Math.round(559  * SCALE), hint: '2 carteles por hoja A5 apaisado' },
        a4: { iframeH: 1123, containerH: Math.round(1123 * SCALE), hint: '1 cartel por hoja A4 vertical' },
    };

    @if($saved['layout'] === 'a4')
    (function() {
        const l = LAYOUTS.a4;
        iframe.style.height = l.iframeH + 'px';
        wrap.style.height   = l.containerH + 'px';
    })();
    @endif

    window['onLayoutChange_{{ $branch->id }}'] = function (layout) {
        const l = LAYOUTS[layout] || LAYOUTS.a5;
        iframe.style.height = l.iframeH + 'px';
        wrap.style.height   = l.containerH + 'px';
        hint.innerHTML      = '<i class="fa-solid fa-scissors mr-1"></i>' + l.hint;
    };

    function buildParams(isPreview) {
        return new URLSearchParams({
            scheme:        formEl.querySelector('[name="qr_scheme"]:checked')?.value         ?? 'blue',
            layout:        formEl.querySelector('[name="qr_layout"]:checked')?.value         ?? 'a5',
            qr_size:       formEl.querySelector('[name="qr_qr_size"]:checked')?.value        ?? 'md',
            headline_size: formEl.querySelector('[name="qr_headline_size"]:checked')?.value  ?? 'md',
            instr_size:    formEl.querySelector('[name="qr_instr_size"]:checked')?.value     ?? 'md',
            logo_size:     formEl.querySelector('[name="qr_logo_size"]:checked')?.value      ?? 'md',
            logo_position: formEl.querySelector('[name="qr_logo_position"]:checked')?.value  ?? 'center',
            headline:      document.getElementById('input-headline-{{ $branch->id }}').value    || 'Verificá tu precio',
            instruction:   document.getElementById('input-instruction-{{ $branch->id }}').value || '',
            show_logo:     document.getElementById('toggle-logo-{{ $branch->id }}').checked    ? '1' : '0',
            show_branch:   document.getElementById('toggle-branch-{{ $branch->id }}').checked  ? '1' : '0',
            ...(isPreview ? { preview: '1' } : {}),
        });
    }

    window['schedulePreviewUpdate_{{ $branch->id }}'] = function () {
        clearTimeout(debounce);
        debounce = setTimeout(function () {
            const selected = formEl.querySelector('[name="qr_scheme"]:checked')?.value;
            formEl.querySelectorAll('.scheme-swatch-{{ $branch->id }}').forEach(el => {
                const active = el.dataset.scheme === selected;
                el.classList.toggle('ring-2',            active);
                el.classList.toggle('ring-offset-2',     active);
                el.classList.toggle('ring-slate-500',    active);
                el.classList.toggle('!border-slate-500', active);
            });
            iframe.src = BASE_URL + '?' + buildParams(true).toString() + '&_t=' + Date.now();
        }, 350);
    };

    btnPrint.addEventListener('click', function () {
        window.open(BASE_URL + '?' + buildParams(false).toString(), '_blank');
    });
})();
</script>
@endpush
