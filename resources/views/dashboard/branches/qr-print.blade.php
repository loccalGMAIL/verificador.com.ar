<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR – {{ $branch->store->name }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">

    <style>
        /* ── Reset ──────────────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        /* ── Página ─────────────────────────────────────────── */
        html, body {
            width: 100%;
            height: 100%;
            background: #e2e8f0;          /* gris para la pantalla, oculto en impresión */
            font-family: 'Inter', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        /* ── Tarjeta ────────────────────────────────────────── */
        .card {
            background: #ffffff;
            width: 105mm;                 /* A6 landscape → A5 portrait */
            min-height: 148mm;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0,0,0,.18);
            display: flex;
            flex-direction: column;
        }

        /* ── Cabecera (azul) ────────────────────────────────── */
        .header {
            background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 100%);
            padding: 18px 20px 16px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }

        .header-logo {
            max-height: 52px;
            max-width: 180px;
            object-fit: contain;
            filter: drop-shadow(0 1px 3px rgba(0,0,0,.4));
        }

        .header-name {
            color: #ffffff;
            font-size: 22px;
            font-weight: 900;
            letter-spacing: .5px;
            text-align: center;
            line-height: 1.1;
            text-transform: uppercase;
            text-shadow: 0 1px 4px rgba(0,0,0,.3);
        }

        .header-branch {
            color: #bfdbfe;              /* blue-200 */
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            text-align: center;
        }

        /* ── Cuerpo ─────────────────────────────────────────── */
        .body {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 14px 20px 10px;
            gap: 10px;
        }

        .headline {
            font-size: 19px;
            font-weight: 900;
            color: #1e3a8a;
            text-align: center;
            letter-spacing: .3px;
            text-transform: uppercase;
            line-height: 1.15;
        }

        .qr-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 6px;
            background: #ffffff;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
        }

        .qr-wrap svg {
            width: 64mm;
            height: 64mm;
            display: block;
        }

        /* ── Pie ────────────────────────────────────────────── */
        .footer {
            padding: 8px 20px 16px;
            text-align: center;
        }

        .instruction {
            font-size: 11.5px;
            color: #475569;
            line-height: 1.5;
            font-weight: 500;
        }

        .url-hint {
            font-size: 9px;
            color: #94a3b8;
            margin-top: 6px;
            font-family: monospace;
            word-break: break-all;
        }

        .powered {
            margin-top: 8px;
            font-size: 8.5px;
            color: #cbd5e1;
            letter-spacing: .5px;
        }

        /* ── Impresión ──────────────────────────────────────── */
        @page {
            size: A6 portrait;
            margin: 0;
        }

        @media print {
            html, body {
                background: transparent;
                min-height: unset;
            }

            .card {
                width: 100%;
                min-height: 100vh;
                border-radius: 0;
                box-shadow: none;
            }

            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="card">

    {{-- ── CABECERA ── --}}
    <div class="header">
        @if($logoBase64)
            <img src="data:{{ $logoMime }};base64,{{ $logoBase64 }}"
                 alt="{{ $branch->store->name }}"
                 class="header-logo">
        @endif

        <div class="header-name">{{ $branch->store->name }}</div>

        @if($branch->store->branches()->count() > 1)
            <div class="header-branch">{{ $branch->name }}</div>
        @endif
    </div>

    {{-- ── CUERPO ── --}}
    <div class="body">
        <div class="headline">Verificá tu precio</div>

        <div class="qr-wrap">
            {!! $svg !!}
        </div>
    </div>

    {{-- ── PIE ── --}}
    <div class="footer">
        <p class="instruction">
            Escaneá el código con tu celular<br>
            para verificar el precio al instante
        </p>
        <p class="powered">verificador.com.ar</p>
    </div>

</div>

{{-- ── Auto-print + auto-close ── --}}
<script>
    // Disparar el diálogo de impresión ni bien la página carga completamente
    window.addEventListener('load', function () {
        // Pequeño delay para que los estilos y el SVG rendericen primero
        setTimeout(function () {
            window.print();
        }, 400);
    });

    // Cerrar la pestaña cuando el usuario cierre o cancele el diálogo de impresión
    window.addEventListener('afterprint', function () {
        window.close();
    });
</script>

</body>
</html>
