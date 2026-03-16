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
        #reader video { border-radius: 12px; width: 100% !important; }
    </style>
</head>
<body class="bg-slate-900 text-white min-h-screen flex flex-col">

    {{-- Header --}}
    <header class="px-4 py-4 flex items-center gap-2">
        <svg viewBox="0 0 36 36" class="w-6 h-6 flex-none" aria-hidden="true">
            <circle cx="18" cy="18" r="14" fill="white" stroke="#2563eb" stroke-width="2.5"/>
            <path d="M11 19 L16 24 L33 8" fill="none" stroke="#10b981" stroke-width="4"
                  stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <span class="text-sm font-semibold text-slate-300">verificador.com.ar</span>
    </header>

    <main class="flex-1 flex flex-col items-center justify-start px-4 pb-8 pt-4 max-w-md mx-auto w-full">

        <h1 class="text-xl font-bold mb-1 text-center">Consultá el precio</h1>
        <p class="text-slate-400 text-sm text-center mb-6">
            Apuntá la cámara al código de barras del producto
        </p>

        {{-- Lector --}}
        <div class="w-full rounded-xl overflow-hidden bg-black mb-6" id="reader"></div>

        {{-- Nombre del producto --}}
        <div id="result-header" class="hidden w-full mb-3 text-center">
            <p id="result-store" class="text-xs text-slate-500 mb-0.5"></p>
            <p id="result-name" class="text-lg font-bold text-white leading-snug"></p>
        </div>

        {{-- Tabla de precios por lista --}}
        <div id="prices-table" class="hidden w-full space-y-2 mb-4"></div>

        {{-- Sin precio en ninguna lista --}}
        <div id="no-prices-box"
             class="hidden w-full bg-slate-800 rounded-xl p-4 text-center border border-slate-700 mb-4">
            <p class="text-slate-400 text-sm">Este producto no tiene precios cargados.</p>
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

        // ── Scanner ──────────────────────────────────────────────────
        const scanner = new Html5Qrcode("reader");

        scanner.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: { width: 280, height: 120 } },
            onScanSuccess,
            () => {}
        ).catch(() => {
            showError('No se pudo acceder a la cámara. Verificá los permisos del navegador.');
            document.getElementById('scan-again').classList.remove('hidden');
        });

        // ── Scan success ─────────────────────────────────────────────
        async function onScanSuccess(barcode) {
            if (!scanning) return;
            scanning = false;
            clearResults();

            try {
                const res  = await fetch(API + encodeURIComponent(barcode));
                const data = await res.json();

                if (!data.found) {
                    showError(data.error || 'Producto no encontrado en este comercio.');
                    document.getElementById('scan-again').classList.remove('hidden');
                    return;
                }

                // Nombre y comercio
                document.getElementById('result-store').textContent = data.store_name || '';
                document.getElementById('result-name').textContent  = data.name;
                document.getElementById('result-header').classList.remove('hidden');

                // Precios
                const prices = data.prices || [];
                const table  = document.getElementById('prices-table');

                const available = prices.filter(p => p.available);
                const unavail   = prices.filter(p => !p.available);

                if (available.length === 0 && unavail.length === 0) {
                    document.getElementById('no-prices-box').classList.remove('hidden');
                } else {
                    // Primero los disponibles
                    available.forEach(p => table.appendChild(buildPriceRow(p)));
                    // Luego los no disponibles (si hay más de una lista)
                    if (prices.length > 1) {
                        unavail.forEach(p => table.appendChild(buildPriceRow(p)));
                    }
                    table.classList.remove('hidden');
                }

                document.getElementById('scan-again').classList.remove('hidden');

            } catch {
                showError('Error de conexión. Intentá de nuevo.');
                document.getElementById('scan-again').classList.remove('hidden');
            }
        }

        // ── Construir fila de precio ─────────────────────────────────
        function buildPriceRow(p) {
            const row = document.createElement('div');
            row.className = 'flex items-center justify-between rounded-xl px-4 py-3 border ' +
                (p.available
                    ? 'bg-slate-800 border-slate-700'
                    : 'bg-slate-900 border-slate-800');

            if (p.available) {
                row.innerHTML = `
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wide">
                        <i class="fa-solid fa-tags mr-1.5 text-emerald-600"></i>${esc(p.list_name)}
                    </span>
                    <span class="text-2xl font-black text-emerald-400">${esc(p.price)}</span>`;
            } else {
                row.innerHTML = `
                    <span class="text-xs text-slate-600 uppercase tracking-wide">
                        <i class="fa-solid fa-tags mr-1.5"></i>${esc(p.list_name)}
                    </span>
                    <span class="text-sm text-slate-700 italic">No disponible</span>`;
            }

            return row;
        }

        // ── Helpers ──────────────────────────────────────────────────
        function clearResults() {
            ['result-header','prices-table','no-prices-box','error-box','scan-again']
                .forEach(id => document.getElementById(id).classList.add('hidden'));
            document.getElementById('prices-table').innerHTML = '';
        }

        function showError(msg) {
            document.getElementById('error-box').classList.remove('hidden');
            document.getElementById('error-msg').textContent = msg;
        }

        function scanAgain() {
            scanning = true;
            clearResults();
        }

        function esc(str) {
            return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
        }
    </script>
</body>
</html>
