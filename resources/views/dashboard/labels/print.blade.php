@php
    $isA4 = $config['print_mode'] === 'a4';

    $columns    = (int) ($config['columns']    ?? 3);
    $marginMm   = (int) ($config['margin_mm']  ?? 5);
    $spacingMm  = (int) ($config['spacing_mm'] ?? 3);
    $labelSize  = $config['label_size']         ?? '40x25';
    $nameFontSize = $config['name_font_size']   ?? 'md';
    $barcodeHeight= $config['barcode_height']   ?? 'md';
    $showNumber   = ($config['show_barcode_number'] ?? '1') === '1';

    $fontSizeMap    = ['sm' => '10px', 'md' => '13px', 'lg' => '16px'];
    $bcHeightMap    = ['sm' => 30,     'md' => 45,     'lg' => 60    ];
    $nameFontSizeCss= $fontSizeMap[$nameFontSize] ?? '13px';
    $bcHeightPx     = $bcHeightMap[$barcodeHeight] ?? 45;

    // Label printer dimensions
    $labelDimensions = [
        '40x25' => [40, 25],
        '58x40' => [58, 40],
        '62x30' => [62, 30],
    ];
    [$labelW, $labelH] = $labelDimensions[$labelSize] ?? [40, 25];
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Etiquetas — verificador.com.ar</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html, body {
            font-family: 'Inter', sans-serif;
            background: #f1f5f9;
        }

        /* ── Contenedor de pantalla ── */
        .screen-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 24px;
            gap: 16px;
        }

        .screen-controls {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            font-size: 13px;
            color: #475569;
        }

        .screen-controls strong { color: #1e293b; }

        .btn-print {
            background: #2563eb;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 8px 18px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .btn-print:hover { background: #1d4ed8; }

        /* ── Hoja de etiquetas ── */
        .label-sheet {
            background: #fff;
            box-shadow: 0 4px 24px rgba(0,0,0,0.12);
            border-radius: 8px;
        }

        @if($isA4)
        .label-sheet {
            width: 210mm;
            min-height: 297mm;
            padding: {{ $marginMm }}mm;
        }
        .label-grid {
            display: grid;
            grid-template-columns: repeat({{ $columns }}, 1fr);
            gap: {{ $spacingMm }}mm;
        }
        @else
        .label-sheet {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }
        @endif

        /* ── Etiqueta individual ── */
        .label-item {
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 3mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 1.5mm;
            overflow: hidden;
            break-inside: avoid;
            page-break-inside: avoid;
        }

        @if(!$isA4)
        .label-item {
            width: {{ $labelW }}mm;
            height: {{ $labelH }}mm;
            flex-shrink: 0;
        }
        @endif

        .label-name {
            font-size: {{ $nameFontSizeCss }};
            font-weight: 600;
            color: #0f172a;
            text-align: center;
            line-height: 1.2;
            max-width: 100%;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .label-barcode {
            max-width: 100%;
        }

        .label-number {
            font-family: monospace;
            font-size: 7px;
            color: #64748b;
            text-align: center;
            letter-spacing: 0.5px;
        }

        /* ── Impresión ── */
        @if($isA4)
        @page { size: A4 portrait; margin: 0; }
        @else
        @page { size: {{ $labelW }}mm {{ $labelH }}mm; margin: 1.5mm; }
        @endif

        @media print {
            html, body {
                background: transparent;
            }
            .screen-controls {
                display: none !important;
            }
            .screen-wrapper {
                padding: 0;
                gap: 0;
                background: transparent;
            }
            .label-sheet {
                box-shadow: none;
                border-radius: 0;
            }

            @if($isA4)
            .label-sheet {
                width: 210mm;
                min-height: 297mm;
            }
            @else
            .label-sheet {
                background: transparent;
                gap: 0;
            }
            .label-item {
                page-break-after: always;
                break-after: page;
                border: none;
                width: 100%;
                height: 100%;
            }
            .label-item:last-child {
                page-break-after: auto;
                break-after: auto;
            }
            @endif
        }
    </style>
</head>
<body>

<div class="screen-wrapper">

    {{-- Controles de pantalla (se ocultan al imprimir) --}}
    <div class="screen-controls">
        <span>
            <strong>{{ $products->count() }}</strong> etiqueta(s) &middot;
            @if($isA4)
                Hoja A4 &middot; {{ $columns }} columna(s)
            @else
                Etiqueta {{ $labelW }}×{{ $labelH }}mm
            @endif
        </span>
        <button class="btn-print" onclick="window.print()">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/>
                <rect x="6" y="14" width="12" height="8"/>
            </svg>
            Imprimir
        </button>
    </div>

    <div class="label-sheet">
        @if($isA4)
        <div class="label-grid">
        @endif

        @foreach($products as $product)
        <div class="label-item">
            <p class="label-name">{{ $product->name }}</p>
            <svg class="label-barcode"
                 id="bc-{{ $product->id }}-{{ $loop->index }}"
                 jsbarcode-value="{{ $product->barcode }}"
                 jsbarcode-format="CODE128"
                 jsbarcode-height="{{ $bcHeightPx }}"
                 jsbarcode-displayvalue="false"
                 jsbarcode-margin="0"
                 jsbarcode-linecolor="#000000"
                 jsbarcode-width="1.5">
            </svg>
            @if($showNumber)
            <p class="label-number">{{ $product->barcode }}</p>
            @endif
        </div>
        @endforeach

        @if($isA4)
        </div>
        @endif
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        JsBarcode('.label-barcode').init();

        // Auto-print after fonts and barcodes are ready
        setTimeout(function () {
            window.print();
        }, 600);
    });

    window.addEventListener('afterprint', function () {
        // Small delay before close to avoid browser blocking
        setTimeout(function () { window.close(); }, 300);
    });
</script>

</body>
</html>
