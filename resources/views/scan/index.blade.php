<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Consultar precio — verificador.com.ar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
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

        {{-- Lector de barcode --}}
        <div class="w-full rounded-xl overflow-hidden bg-black mb-6" id="reader"></div>

        {{-- Resultado --}}
        <div id="result-box" class="hidden w-full bg-slate-800 rounded-xl p-5 text-center border border-slate-700">
            <p id="result-name" class="text-sm text-slate-400 mb-1"></p>
            <p id="result-price" class="text-4xl font-bold text-emerald-400 mb-1"></p>
            <p id="result-currency" class="text-xs text-slate-500"></p>
        </div>

        {{-- Error --}}
        <div id="error-box" class="hidden w-full bg-red-900/40 rounded-xl p-4 text-center border border-red-800">
            <p class="text-red-300 text-sm" id="error-msg">Producto no encontrado.</p>
        </div>

        {{-- Botón escanear otro --}}
        <button id="scan-again" onclick="scanAgain()"
                class="hidden mt-4 text-blue-400 text-sm underline">
            Escanear otro producto
        </button>
    </main>

    {{-- html5-qrcode via CDN --}}
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

    <script>
        const TOKEN = "{{ $token }}";
        const API   = `/api/scan/${TOKEN}/`;

        let scanner = null;
        let scanning = true;

        function startScanner() {
            scanner = new Html5Qrcode("reader");

            scanner.start(
                { facingMode: "environment" },
                { fps: 10, qrbox: { width: 280, height: 120 } },
                onScanSuccess,
                () => {} // errores de frame ignorados
            ).catch(err => {
                document.getElementById('error-box').classList.remove('hidden');
                document.getElementById('error-msg').textContent =
                    'No se pudo acceder a la cámara. Verificá los permisos del navegador.';
            });
        }

        async function onScanSuccess(barcode) {
            if (!scanning) return;
            scanning = false;

            try {
                const res  = await fetch(API + encodeURIComponent(barcode));
                const data = await res.json();

                document.getElementById('result-box').classList.remove('hidden');
                document.getElementById('error-box').classList.add('hidden');
                document.getElementById('scan-again').classList.remove('hidden');

                if (data.found) {
                    document.getElementById('result-name').textContent    = data.name;
                    document.getElementById('result-price').textContent   = data.price;
                    document.getElementById('result-currency').textContent = data.currency_label;
                } else {
                    document.getElementById('result-box').classList.add('hidden');
                    document.getElementById('error-box').classList.remove('hidden');
                    document.getElementById('error-msg').textContent = 'Producto no encontrado en este comercio.';
                }
            } catch {
                document.getElementById('error-box').classList.remove('hidden');
                document.getElementById('error-msg').textContent = 'Error de conexión. Intentá de nuevo.';
                document.getElementById('scan-again').classList.remove('hidden');
            }
        }

        function scanAgain() {
            scanning = true;
            document.getElementById('result-box').classList.add('hidden');
            document.getElementById('error-box').classList.add('hidden');
            document.getElementById('scan-again').classList.add('hidden');
        }

        startScanner();
    </script>
</body>
</html>
