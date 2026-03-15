<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR – {{ $branch->store->name }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">

    @php
        $isPreview   = request()->boolean('preview');
        $scheme      = request('scheme', 'blue');
        $headline    = request('headline', 'Verificá tu precio');
        $instruction = request('instruction', "Escaneá el código con tu celular\npara verificar el precio al instante");
        $showLogo    = request('show_logo', '1') === '1';
        $showBranch  = request('show_branch', '1') === '1';

        $palettes = [
            'blue'   => ['from' => '#1e3a8a', 'to' => '#1d4ed8', 'sub' => '#bfdbfe', 'text' => '#1e3a8a'],
            'green'  => ['from' => '#065f46', 'to' => '#059669', 'sub' => '#a7f3d0', 'text' => '#065f46'],
            'dark'   => ['from' => '#111827', 'to' => '#374151', 'sub' => '#d1d5db', 'text' => '#111827'],
            'purple' => ['from' => '#4c1d95', 'to' => '#7c3aed', 'sub' => '#ddd6fe', 'text' => '#4c1d95'],
            'orange' => ['from' => '#7c2d12', 'to' => '#ea580c', 'sub' => '#fed7aa', 'text' => '#7c2d12'],
        ];
        $p = $palettes[$scheme] ?? $palettes['blue'];
    @endphp

    <style>
        /* ── Reset ─────────────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        /* ── Página (vista normal en pantalla) ─────────────── */
        html, body {
            width: 100%;
            min-height: 100vh;
            background: #e2e8f0;
            font-family: 'Inter', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        /* ── Modo preview en iframe ─────────────────────────── */
        body.preview-mode {
            background: #ffffff;
            padding: 0;
            min-height: unset;
            width: 794px;
            height: 559px;
            display: block;
            overflow: hidden;
        }
        body.preview-mode .sheet {
            width: 794px !important;
            height: 559px !important;
            border-radius: 0 !important;
            box-shadow: none !important;
        }

        /* ── Hoja A5 apaisado ───────────────────────────────── */
        .sheet {
            background: #ffffff;
            width: 210mm;
            height: 148mm;
            display: flex;
            flex-direction: row;
            box-shadow: 0 8px 32px rgba(0,0,0,.18);
            border-radius: 8px;
            overflow: hidden;
        }

        /* ── Línea de corte central ─────────────────────────── */
        .sheet-divider {
            width: 1px;
            flex-shrink: 0;
            background: repeating-linear-gradient(
                to bottom,
                #cbd5e1 0px, #cbd5e1 6px,
                transparent 6px, transparent 12px
            );
        }

        /* ── Tarjeta individual ─────────────────────────────── */
        .card {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            min-width: 0;
        }

        /* ── Cabecera ───────────────────────────────────────── */
        .header {
            background: linear-gradient(135deg, {{ $p['from'] }} 0%, {{ $p['to'] }} 100%);
            /* Forzar impresión del fondo en todos los navegadores */
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            color-adjust: exact;

            padding: 8px 14px 7px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            flex-shrink: 0;
        }

        .header-logo {
            max-height: 28px;
            max-width: 120px;
            object-fit: contain;
            filter: drop-shadow(0 1px 2px rgba(0,0,0,.4));
        }

        .header-name {
            color: #ffffff;
            font-size: 13px;
            font-weight: 900;
            letter-spacing: .4px;
            text-align: center;
            line-height: 1.1;
            text-transform: uppercase;
            text-shadow: 0 1px 3px rgba(0,0,0,.3);
        }

        .header-branch {
            color: {{ $p['sub'] }};
            font-size: 8px;
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
            padding: 6px 12px 4px;
            gap: 5px;
            overflow: hidden;
        }

        .headline {
            font-size: 12px;
            font-weight: 900;
            color: {{ $p['text'] }};
            text-align: center;
            letter-spacing: .3px;
            text-transform: uppercase;
            line-height: 1.15;
            flex-shrink: 0;
        }

        .qr-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 4px;
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            border-radius: 5px;
            flex-shrink: 0;
        }

        .qr-wrap svg {
            width: 48mm;
            height: 48mm;
            display: block;
        }

        /* ── Pie ────────────────────────────────────────────── */
        .footer {
            padding: 4px 12px 8px;
            text-align: center;
            flex-shrink: 0;
        }

        .instruction {
            font-size: 9px;
            color: #475569;
            line-height: 1.5;
            font-weight: 500;
            white-space: pre-line;
        }

        .powered {
            margin-top: 4px;
            font-size: 7px;
            color: #cbd5e1;
            letter-spacing: .5px;
        }

        /* ── Impresión ──────────────────────────────────────── */
        @page {
            size: A5 landscape;
            margin: 0;
        }

        @media print {
            html, body {
                background: transparent;
                padding: 0;
                min-height: unset;
                display: block;
            }

            .sheet {
                width: 210mm !important;
                height: 148mm !important;
                border-radius: 0;
                box-shadow: none;
            }

            .sheet-divider {
                background: repeating-linear-gradient(
                    to bottom,
                    #94a3b8 0px, #94a3b8 4px,
                    transparent 4px, transparent 10px
                );
            }
        }
    </style>
</head>
<body class="{{ $isPreview ? 'preview-mode' : '' }}">

<div class="sheet">

    {{-- ══ TARJETA IZQUIERDA ══ --}}
    <div class="card">
        <div class="header">
            @if($showLogo && $logoBase64)
                <img src="data:{{ $logoMime }};base64,{{ $logoBase64 }}"
                     alt="{{ $branch->store->name }}"
                     class="header-logo">
            @endif
            <div class="header-name">{{ $branch->store->name }}</div>
            @if($showBranch && $branch->store->branches()->count() > 1)
                <div class="header-branch">{{ $branch->name }}</div>
            @endif
        </div>

        <div class="body">
            <div class="headline">{{ $headline }}</div>
            <div class="qr-wrap">{!! $svg !!}</div>
        </div>

        <div class="footer">
            <p class="instruction">{{ $instruction }}</p>
            <p class="powered">verificador.com.ar</p>
        </div>
    </div>

    {{-- ── Línea de corte ── --}}
    <div class="sheet-divider"></div>

    {{-- ══ TARJETA DERECHA (copia idéntica) ══ --}}
    <div class="card">
        <div class="header">
            @if($showLogo && $logoBase64)
                <img src="data:{{ $logoMime }};base64,{{ $logoBase64 }}"
                     alt="{{ $branch->store->name }}"
                     class="header-logo">
            @endif
            <div class="header-name">{{ $branch->store->name }}</div>
            @if($showBranch && $branch->store->branches()->count() > 1)
                <div class="header-branch">{{ $branch->name }}</div>
            @endif
        </div>

        <div class="body">
            <div class="headline">{{ $headline }}</div>
            <div class="qr-wrap">{!! $svg !!}</div>
        </div>

        <div class="footer">
            <p class="instruction">{{ $instruction }}</p>
            <p class="powered">verificador.com.ar</p>
        </div>
    </div>

</div>

@if(!$isPreview)
<script>
    window.addEventListener('load', function () {
        setTimeout(function () { window.print(); }, 400);
    });
    window.addEventListener('afterprint', function () {
        window.close();
    });
</script>
@endif

</body>
</html>
