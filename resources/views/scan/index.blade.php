<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Consultar precio — verificador.com.ar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        #reader { width: 100%; }
        #reader video { border-radius: 0 0 12px 12px; width: 100% !important; }
        #camera-accordion { overflow: hidden; transition: max-height 0.4s ease; max-height: 600px; }
        #camera-accordion.collapsed { max-height: 0; }
    </style>
</head>
@php
    $bgColor              = $store->scan_bg_color              ?? '#0f172a';
    $accentColor          = $store->scan_accent_color          ?? '#34d399';
    $secondaryColor       = $store->scan_secondary_color       ?? '#93c5fd';
    $wholesaleCardColor   = $store->scan_wholesale_card_color  ?? '#172033';
    $cardStyle            = $store->scan_card_style            ?? 'dark';
    $fontSize             = $store->scan_font_size             ?? 'lg';
    $showLogo             = $store->scan_show_logo             ?? false;
    $logoSize             = $store->scan_logo_size             ?? 'md';
    $headerFontSize       = $store->scan_header_font_size      ?? 'sm';
    $showStoreName        = $store->scan_show_store_name       ?? true;
    $showBranchName       = $store->scan_show_branch_name      ?? true;
    $headerText           = $store->scan_header_text           ?? 'Consultá el precio';

    $cardBg     = $cardStyle === 'light' ? '#f1f5f9' : '#1e293b';
    $cardBorder = $cardStyle === 'light' ? '#cbd5e1' : '#334155';
    $cardText   = $cardStyle === 'light' ? '#1e293b' : '#ffffff';

    $fontSizeMap = [
        'sm' => 'text-3xl',
        'md' => 'text-4xl',
        'lg' => 'text-5xl',
        'xl' => 'text-7xl',
    ];
    $priceClass = $fontSizeMap[$fontSize] ?? 'text-5xl';

    $logoSizeMap = [
        'sm' => 'h-6',
        'md' => 'h-8',
        'lg' => 'h-12',
        'xl' => 'h-16',
    ];
    $logoClass = $logoSizeMap[$logoSize] ?? 'h-8';

    $headerFontMap = [
        'xs' => ['name' => 'text-xs',  'branch' => 'text-[10px]'],
        'sm' => ['name' => 'text-sm',  'branch' => 'text-xs'],
        'md' => ['name' => 'text-base','branch' => 'text-sm'],
        'lg' => ['name' => 'text-lg',  'branch' => 'text-sm'],
    ];
    $headerFont = $headerFontMap[$headerFontSize] ?? $headerFontMap['sm'];
@endphp
<body class="min-h-screen flex flex-col text-white" style="background-color: {{ $bgColor }};">

    {{-- Header --}}
    <header class="px-4 py-4 flex items-center gap-3">
        {{-- Logo o ícono fallback --}}
        @if($showLogo && $logoDataUri)
            <img src="{{ $logoDataUri }}" alt="{{ $store->name }}"
                 class="{{ $logoClass }} max-w-[140px] object-contain flex-shrink-0">
        @else
            <svg viewBox="0 0 36 36" class="w-6 h-6 flex-none" aria-hidden="true">
                <circle cx="18" cy="18" r="14" fill="white" stroke="#2563eb" stroke-width="2.5"/>
                <path d="M11 19 L16 24 L33 8" fill="none" stroke="#10b981" stroke-width="4"
                      stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        @endif
        {{-- Nombre del comercio y sucursal, en la misma línea que el logo --}}
        <div class="min-w-0">
            @if($showStoreName && $store)
                <p class="{{ $headerFont['name'] }} font-bold text-white leading-tight truncate">{{ $store->name }}</p>
            @endif
            @if($showBranchName && $branch)
                <p class="{{ $headerFont['branch'] }} text-slate-400 leading-tight truncate">{{ $branch->name }}</p>
            @endif
            @if(!$showStoreName && !$showBranchName)
                <span class="text-sm font-semibold text-slate-300">verificador.com.ar</span>
            @endif
        </div>
    </header>

    <main class="flex-1 flex flex-col items-center justify-start px-4 pb-8 pt-4 max-w-md mx-auto w-full">

        <h1 class="text-xl font-bold mb-1 text-center">{{ $headerText }}</h1>

        {{-- Acordeón cámara --}}
        <div class="w-full rounded-xl border border-white/10 overflow-hidden mb-6">
            <button id="camera-toggle" onclick="toggleCamera()"
                    class="w-full flex items-center justify-between px-4 py-3 bg-white/5 text-sm font-medium text-slate-300">
                <span><i class="fa-solid fa-barcode mr-2 text-slate-400"></i>Apuntá al código de barras</span>
                <i id="camera-chevron" class="fa-solid fa-chevron-up text-xs text-slate-400 transition-transform duration-300"></i>
            </button>
            <div id="camera-accordion">
                <div id="reader"></div>
            </div>
        </div>

        {{-- Acordeón búsqueda manual --}}
        <div class="w-full rounded-xl border border-white/10 overflow-hidden mb-6">
            <button id="manual-toggle" onclick="toggleManual()"
                    class="w-full flex items-center justify-between px-4 py-3 bg-white/5 text-sm font-medium text-slate-300">
                <span><i class="fa-solid fa-keyboard mr-2 text-slate-400"></i>Buscar por código</span>
                <i id="manual-chevron" class="fa-solid fa-chevron-down text-xs text-slate-400 transition-transform duration-300"></i>
            </button>
            <div id="manual-accordion" style="max-height:0; overflow:hidden; transition: max-height 0.4s ease;">
                <div class="px-4 py-3 flex gap-2"
                     style="background-color: {{ $cardBg }}; border-top: 1px solid {{ $cardBorder }};">
                    <input id="manual-input" type="tel" inputmode="numeric" pattern="[0-9]*"
                           placeholder="Ingresá el código de barras"
                           class="flex-1 rounded-lg px-3 py-2 text-sm outline-none"
                           style="background-color: {{ $bgColor }}; border: 1px solid {{ $cardBorder }}; color: {{ $cardText }};" />
                    <button onclick="searchManual()"
                            class="rounded-lg px-4 py-2 text-sm font-semibold text-white transition"
                            style="background-color: {{ $accentColor }}; color: {{ $cardStyle === 'light' ? '#1e293b' : '#ffffff' }};">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- Nombre del producto --}}
        <div id="result-header" class="hidden w-full mb-4 text-center">
            <p id="result-store" class="text-xs text-slate-500 mb-0.5"></p>
            <p id="result-name" class="text-lg font-bold text-white leading-snug"></p>
        </div>

        {{-- Precio principal --}}
        <div id="retail-box" class="hidden w-full rounded-xl px-5 py-4 mb-3"
             style="background-color: {{ $cardBg }}; border: 1px solid {{ $cardBorder }}; color: {{ $cardText }};">
            <p id="retail-label" class="text-xs font-semibold uppercase tracking-wide mb-1" style="color: {{ $cardText }}; opacity: 0.6;">
                <i class="fa-solid fa-tags mr-1.5"></i>Precio
            </p>
            <p id="retail-price" class="{{ $priceClass }} font-black" style="color: {{ $accentColor }};"></p>
        </div>

        {{-- Precio mayorista --}}
        <div id="wholesale-box" class="hidden w-full rounded-xl px-5 py-3 mb-4"
             style="background-color: {{ $wholesaleCardColor }}; border: 1px solid {{ $cardBorder }};">
            <p id="wholesale-label" class="text-xs font-semibold uppercase tracking-wide mb-0.5" style="color: {{ $cardText }}; opacity: 0.6;">
                <i class="fa-solid fa-tags mr-1.5"></i>Mayorista
            </p>
            <p id="wholesale-price" class="{{ $priceClass }} font-black" style="color: {{ $secondaryColor }};"></p>
        </div>

        {{-- Sin precio --}}
        <div id="no-price-box"
             class="hidden w-full rounded-xl p-4 text-center mb-4"
             style="background-color: {{ $cardBg }}; border: 1px solid {{ $cardBorder }};">
            <p class="text-slate-400 text-sm">Este producto no tiene precio cargado.</p>
        </div>

        {{-- Error --}}
        <div id="error-box"
             class="hidden w-full bg-red-900/40 rounded-xl p-4 text-center border border-red-800 mb-4">
            <p class="text-red-300 text-sm" id="error-msg">Producto no encontrado.</p>
        </div>

        {{-- Botón escanear otro --}}
        <button id="scan-again" onclick="scanAgain()"
                class="hidden mt-2 bg-blue-600 text-white text-sm font-semibold
                       px-5 py-2.5 rounded-xl hover:bg-blue-700 transition">
            <i class="fa-solid fa-barcode mr-2"></i>Escanear otro producto
        </button>
    </main>

    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        const TOKEN = "{{ $token }}";
        const API   = `/api/scan/${TOKEN}/`;
        let scanning = true;
        let cameraOpen = true;
        let manualOpen = false;

        // ── Acordeón cámara ───────────────────────────────────────────
        function toggleCamera() {
            if (cameraOpen) {
                collapseCamera();
            } else {
                if (manualOpen) collapseManual();
                expandCamera();
            }
        }

        function collapseCamera() {
            const acc     = document.getElementById('camera-accordion');
            const chevron = document.getElementById('camera-chevron');
            acc.classList.add('collapsed');
            chevron.style.transform = 'rotate(180deg)';
            cameraOpen = false;
        }

        function expandCamera() {
            const acc     = document.getElementById('camera-accordion');
            const chevron = document.getElementById('camera-chevron');
            acc.classList.remove('collapsed');
            chevron.style.transform = '';
            cameraOpen = true;
        }

        // ── Acordeón manual ───────────────────────────────────────────
        function toggleManual() {
            if (manualOpen) {
                collapseManual();
            } else {
                if (cameraOpen) collapseCamera();
                expandManual();
            }
        }

        function collapseManual() {
            const acc     = document.getElementById('manual-accordion');
            const chevron = document.getElementById('manual-chevron');
            acc.style.maxHeight = '0';
            chevron.style.transform = '';
            manualOpen = false;
        }

        function expandManual() {
            const acc     = document.getElementById('manual-accordion');
            const chevron = document.getElementById('manual-chevron');
            acc.style.maxHeight = acc.scrollHeight + 'px';
            chevron.style.transform = 'rotate(180deg)';
            manualOpen = true;
            document.getElementById('manual-input').focus();
        }

        // ── Scanner ──────────────────────────────────────────────────
        const scanner = new Html5Qrcode("reader");

        scanner.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: { width: 140, height: 60 } },
            onScanSuccess,
            () => {}
        ).catch(() => {
            showError('No se pudo acceder a la cámara. Verificá los permisos del navegador.');
            document.getElementById('scan-again').classList.remove('hidden');
        });

        // ── Scan handlers ─────────────────────────────────────────────
        async function onScanSuccess(barcode) {
            if (!scanning) return;
            scanning = false;
            clearResults();
            collapseCamera();
            await lookupBarcode(barcode);
        }

        async function searchManual() {
            const barcode = document.getElementById('manual-input').value.trim();
            if (!barcode) return;
            clearResults();
            collapseManual();
            await lookupBarcode(barcode);
            document.getElementById('manual-input').value = '';
        }

        // ── Lookup ────────────────────────────────────────────────────
        async function lookupBarcode(barcode) {
            try {
                const res  = await fetch(API + encodeURIComponent(barcode));
                const data = await res.json();

                if (!data.found) {
                    showError(data.error || 'Producto no encontrado en este comercio.');
                    document.getElementById('scan-again').classList.remove('hidden');
                    return;
                }

                // Nombre del producto
                if (data.name) {
                    document.getElementById('result-name').textContent = data.name;
                    document.getElementById('result-header').classList.remove('hidden');
                }

                // Precio principal
                if (data.retail_price !== undefined && data.retail_price !== null) {
                    const retailBox   = document.getElementById('retail-box');
                    const retailLabel = document.getElementById('retail-label');
                    const retailPrice = document.getElementById('retail-price');

                    retailLabel.innerHTML = `<i class="fa-solid fa-tags mr-1.5"></i>${esc(data.retail_label || 'Precio')}`;
                    retailPrice.textContent = formatPrice(data.retail_price);
                    retailBox.classList.remove('hidden');
                } else {
                    document.getElementById('no-price-box').classList.remove('hidden');
                }

                // Precio mayorista
                if (data.show_wholesale && data.wholesale_price !== undefined) {
                    const wsBox   = document.getElementById('wholesale-box');
                    const wsLabel = document.getElementById('wholesale-label');
                    const wsPrice = document.getElementById('wholesale-price');

                    wsLabel.innerHTML = `<i class="fa-solid fa-tags mr-1.5"></i>${esc(data.wholesale_label || 'Mayorista')}`;
                    wsPrice.textContent = formatPrice(data.wholesale_price);
                    wsBox.classList.remove('hidden');
                }

                document.getElementById('scan-again').classList.remove('hidden');

            } catch {
                showError('Error de conexión. Intentá de nuevo.');
                document.getElementById('scan-again').classList.remove('hidden');
            }
        }

        // ── Helpers ──────────────────────────────────────────────────
        function formatPrice(value) {
            return '$ ' + Number(value).toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function clearResults() {
            ['result-header','retail-box','wholesale-box','no-price-box','error-box','scan-again']
                .forEach(id => document.getElementById(id).classList.add('hidden'));
        }

        function showError(msg) {
            document.getElementById('error-box').classList.remove('hidden');
            document.getElementById('error-msg').textContent = msg;
        }

        function scanAgain() {
            scanning = true;
            clearResults();
            expandCamera();
        }

        function esc(str) {
            return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
        }

        // ── Enter en input manual ─────────────────────────────────────
        document.getElementById('manual-input')
            .addEventListener('keydown', e => { if (e.key === 'Enter') searchManual(); });
    </script>

    {{-- Publicidad fija en la parte inferior --}}
    <div style="position:fixed;bottom:0;left:0;right:0;z-index:50;text-align:center;padding:6px 12px;background:rgba(15,23,42,0.82);backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px);">
        <a href="https://verificador.com.ar" target="_blank" rel="noopener"
           style="display:inline-flex;align-items:center;gap:6px;text-decoration:none;">
            <svg viewBox="0 0 36 36" style="width:14px;height:14px;flex-shrink:0" aria-hidden="true">
                <circle cx="18" cy="18" r="14" fill="none" stroke="#2563eb" stroke-width="3"></circle>
                <path d="M11 19 L16 24 L33 8" fill="none" stroke="#10b981" stroke-width="4.5" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
            <span style="font-size:11px;font-weight:600;color:#94a3b8;letter-spacing:.02em;">
                Precios en tiempo real con
                <span style="color:#60a5fa;">verificador</span><span style="color:#64748b;">.com.ar</span>
            </span>
        </a>
    </div>
</body>
</html>
