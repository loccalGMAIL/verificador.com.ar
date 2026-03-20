<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR – {{ $branch->store->name }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">

    @php
        $isPreview    = request()->boolean('preview');
        $layout       = request('layout',        $defaults['layout']);
        $headline     = request('headline',      $defaults['headline']);
        $instruction  = request('instruction',   $defaults['instruction']);
        $showLogo     = request('show_logo',     $defaults['show_logo'])   === '1';
        $showBranch   = request('show_branch',   $defaults['show_branch']) === '1';
        $logoPosition = request('logo_position', $defaults['logo_position']);

        // Tamaños: también usan defaults guardados
        $savedQrSize       = $defaults['qr_size'];
        $savedHeadlineSize = $defaults['headline_size'];
        $savedInstrSize    = $defaults['instr_size'];
        $savedLogoSize     = $defaults['logo_size'];

        $headerAlign     = match($logoPosition) { 'left' => 'flex-start', 'right' => 'flex-end', default => 'center' };
        $headerTextAlign = match($logoPosition) { 'left' => 'left',       'right' => 'right',    default => 'center' };

        // ── Tamaños configurables ─────────────────────────────────────
        $qrSizeMap = [
            'sm' => ['a5' => '36mm', 'a4' => '55mm'],
            'md' => ['a5' => '48mm', 'a4' => '72mm'],
            'lg' => ['a5' => '60mm', 'a4' => '90mm'],
            'xl' => ['a5' => '72mm', 'a4' => '110mm'],
        ];
        $headlineSizeMap = [
            'sm' => ['a5' => '9px',  'a4' => '14px'],
            'md' => ['a5' => '12px', 'a4' => '19px'],
            'lg' => ['a5' => '15px', 'a4' => '24px'],
        ];
        $instrSizeMap = [
            'sm' => ['a5' => '7px',  'a4' => '11px'],
            'md' => ['a5' => '9px',  'a4' => '14px'],
            'lg' => ['a5' => '11px', 'a4' => '17px'],
        ];
        $logoSizeMap = [
            'sm' => ['a5' => '20px', 'a4' => '36px'],
            'md' => ['a5' => '32px', 'a4' => '56px'],
            'lg' => ['a5' => '48px', 'a4' => '80px'],
        ];

        $qrSize       = $qrSizeMap[request('qr_size', $savedQrSize)][$layout]             ?? ($layout === 'a4' ? '72mm' : '48mm');
        $headlineSize = $headlineSizeMap[request('headline_size', $savedHeadlineSize)][$layout] ?? ($layout === 'a4' ? '19px' : '12px');
        $instrSize    = $instrSizeMap[request('instr_size', $savedInstrSize)][$layout]         ?? ($layout === 'a4' ? '14px' : '9px');
        $logoSize     = $logoSizeMap[request('logo_size', $savedLogoSize)][$layout]            ?? ($layout === 'a4' ? '56px' : '32px');

        // ── Layout dimensions ──────────────────────────────────────────
        // A5 landscape: 210mm × 148mm — 2 tarjetas side-by-side
        // A4 portrait:  210mm × 297mm — 1 tarjeta full-page
        $isA4 = $layout === 'a4';

        // Preview mode pixel dimensions (at 96dpi approx)
        $previewW = 794;
        $previewH = $isA4 ? 1123 : 559;

        // ── Color del encabezado (libre) ───────────────────────────────
        $headerColor = request('header_color', $defaults['header_color'] ?? '#1e3a8a');
        if (!preg_match('/^#[0-9a-fA-F]{6}$/', $headerColor)) {
            $headerColor = '#1e3a8a';
        }
        $hr = hexdec(substr($headerColor, 1, 2));
        $hg = hexdec(substr($headerColor, 3, 2));
        $hb = hexdec(substr($headerColor, 5, 2));

        // from: versión oscurecida (×0.6)
        $fromColor = sprintf('#%02x%02x%02x', (int)($hr * 0.6), (int)($hg * 0.6), (int)($hb * 0.6));
        // sub: versión aclarada (mezcla con blanco al 65%)
        $subColor  = sprintf('#%02x%02x%02x',
            min(255, (int)($hr + (255 - $hr) * 0.65)),
            min(255, (int)($hg + (255 - $hg) * 0.65)),
            min(255, (int)($hb + (255 - $hb) * 0.65)));

        $p = ['from' => $fromColor, 'to' => $headerColor, 'sub' => $subColor, 'text' => $headerColor];
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
            width: {{ $previewW }}px;
            height: {{ $previewH }}px;
            display: block;
            overflow: hidden;
        }
        body.preview-mode .sheet {
            @if($isA4)
            width: {{ $previewW }}px !important;
            height: {{ $previewH }}px !important;
            @else
            width: {{ $previewW }}px !important;
            height: {{ $previewH }}px !important;
            @endif
            border-radius: 0 !important;
            box-shadow: none !important;
        }

        /* ── Hoja ───────────────────────────────────────────── */
        .sheet {
            background: #ffffff;
            @if($isA4)
            width: 210mm;
            height: 297mm;
            flex-direction: column;
            @else
            width: 210mm;
            height: 148mm;
            flex-direction: row;
            @endif
            display: flex;
            box-shadow: 0 8px 32px rgba(0,0,0,.18);
            border-radius: 8px;
            overflow: hidden;
        }

        /* ── Línea de corte (solo A5) ───────────────────────── */
        .sheet-divider {
            @if($isA4) display: none; @else
            width: 1px;
            flex-shrink: 0;
            background: repeating-linear-gradient(
                to bottom,
                #cbd5e1 0px, #cbd5e1 6px,
                transparent 6px, transparent 12px
            );
            @endif
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
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            color-adjust: exact;

            padding: @if($isA4) 14px 20px 12px @else 8px 14px 7px @endif;
            display: flex;
            flex-direction: column;
            align-items: {{ $headerAlign }};
            gap: @if($isA4) 6px @else 4px @endif;
            flex-shrink: 0;
        }

        .header-logo {
            max-height: {{ $logoSize }};
            max-width: @if($isA4) 200px @else 120px @endif;
            object-fit: contain;
            filter: drop-shadow(0 1px 2px rgba(0,0,0,.4));
        }

        .header-name {
            color: #ffffff;
            font-size: @if($isA4) 20px @else 13px @endif;
            font-weight: 900;
            letter-spacing: .4px;
            text-align: {{ $headerTextAlign }};
            line-height: 1.1;
            text-transform: uppercase;
            text-shadow: 0 1px 3px rgba(0,0,0,.3);
            width: 100%;
        }

        .header-branch {
            color: {{ $p['sub'] }};
            font-size: @if($isA4) 11px @else 8px @endif;
            font-weight: 600;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            text-align: {{ $headerTextAlign }};
            width: 100%;
        }

        /* ── Cuerpo ─────────────────────────────────────────── */
        .body {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: @if($isA4) 20px 24px 12px @else 6px 12px 4px @endif;
            gap: @if($isA4) 16px @else 5px @endif;
            overflow: hidden;
        }

        .headline {
            font-size: {{ $headlineSize }};
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
            padding: @if($isA4) 8px @else 4px @endif;
            background: #ffffff;
            border: @if($isA4) 2px @else 1.5px @endif solid #e2e8f0;
            border-radius: @if($isA4) 8px @else 5px @endif;
            flex-shrink: 0;
        }

        .qr-wrap svg {
            width: {{ $qrSize }};
            height: {{ $qrSize }};
            display: block;
        }

        /* ── Pie ────────────────────────────────────────────── */
        .footer {
            padding: @if($isA4) 8px 24px 20px @else 4px 12px 8px @endif;
            text-align: center;
            flex-shrink: 0;
        }

        .instruction {
            font-size: {{ $instrSize }};
            color: #475569;
            line-height: 1.5;
            font-weight: 500;
            white-space: pre-line;
        }

        .powered {
            margin-top: @if($isA4) 8px @else 4px @endif;
            font-size: @if($isA4) 9px @else 7px @endif;
            color: #cbd5e1;
            letter-spacing: .5px;
        }

        /* ── Impresión ──────────────────────────────────────── */
        @if($isA4)
        @page { size: A4 portrait; margin: 0; }
        @else
        @page { size: A5 landscape; margin: 0; }
        @endif

        @media print {
            html, body {
                background: transparent;
                padding: 0;
                min-height: unset;
                display: block;
            }

            .sheet {
                @if($isA4)
                width: 210mm !important;
                height: 297mm !important;
                @else
                width: 210mm !important;
                height: 148mm !important;
                @endif
                border-radius: 0;
                box-shadow: none;
            }

            @if(!$isA4)
            .sheet-divider {
                background: repeating-linear-gradient(
                    to bottom,
                    #94a3b8 0px, #94a3b8 4px,
                    transparent 4px, transparent 10px
                );
            }
            @endif
        }
    </style>
</head>
<body class="{{ $isPreview ? 'preview-mode' : '' }}">

<div class="sheet">

    {{-- ══ TARJETA (izquierda en A5, única en A4) ══ --}}
    <div class="card">
        <div class="header">
            @if($showLogo && $logoBase64)
                <img src="data:{{ $logoMime }};base64,{{ $logoBase64 }}"
                     alt="{{ $branch->store->name }}"
                     class="header-logo">
            @endif
            <div class="header-name">{{ $branch->store->name }}</div>
            @if($showBranch)
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

    @if(!$isA4)
    {{-- ── Línea de corte (solo A5) ── --}}
    <div class="sheet-divider"></div>

    {{-- ══ TARJETA DERECHA (copia idéntica, solo A5) ══ --}}
    <div class="card">
        <div class="header">
            @if($showLogo && $logoBase64)
                <img src="data:{{ $logoMime }};base64,{{ $logoBase64 }}"
                     alt="{{ $branch->store->name }}"
                     class="header-logo">
            @endif
            <div class="header-name">{{ $branch->store->name }}</div>
            @if($showBranch)
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
    @endif

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
