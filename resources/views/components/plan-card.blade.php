@props([
    'plan',
    'featured'  => false,
    'variant'   => 'landing',   // 'landing' | 'dashboard'
])

@php
    $featuresMap = [
        'Basic'    => ['Subida de Excel', 'Consulta por escaneo', 'QR del comercio', 'Soporte básico'],
        'Standard' => ['Subida de Excel', 'Consulta por escaneo', 'QR del comercio', 'Historial de importaciones', 'Estadísticas básicas', 'Soporte estándar'],
        'Pro'      => ['Subida de Excel', 'Consulta por escaneo', 'QR del comercio', 'Búsqueda manual', 'Historial de importaciones', 'Estadísticas avanzadas', 'Listas de precios', 'Personalización de app y QR', 'Soporte prioritario'],
        'Business' => ['Subida de Excel', 'Consulta por escaneo', 'QR del comercio', 'Búsqueda manual', 'Historial de importaciones', 'Estadísticas avanzadas', 'Listas de precios', 'Personalización de app y QR', 'Sucursales', 'API con CRM', 'Soporte prioritario'],
    ];

    $isDashboard = $variant === 'dashboard';

    $cardColor  = $featured
        ? ($isDashboard ? 'bg-white border border-blue-400 ring-2 ring-blue-200 shadow-md' : 'bg-blue-600')
        : 'bg-white border border-slate-200';
    $textColor  = $featured && !$isDashboard ? 'text-white'     : 'text-slate-800';
    $subColor   = $featured && !$isDashboard ? 'text-blue-200'  : 'text-slate-400';
    $listColor  = $featured && !$isDashboard ? 'text-blue-100'  : 'text-slate-600';
    $checkColor = $featured && !$isDashboard ? 'text-emerald-300' : 'text-emerald-500';
@endphp

<div {{ $attributes->merge(['class' => "rounded-2xl shadow-sm p-5 flex flex-col relative $cardColor"]) }}>

    @if($featured)
    <span class="absolute -top-3 left-1/2 -translate-x-1/2 text-white text-xs font-bold px-4 py-1 rounded-full whitespace-nowrap
                 {{ $isDashboard ? 'bg-blue-600' : 'bg-emerald-500' }}">
        {{ $isDashboard ? 'Más popular' : 'Recomendado ⭐' }}
    </span>
    @endif

    <h3 class="text-lg font-bold {{ $textColor }} mb-1">{{ $plan->name }}</h3>
    <div class="text-3xl font-extrabold {{ $textColor }} mb-1">
        {{ $plan->formattedPriceArs() }}
        <span class="text-sm font-normal {{ $subColor }}">/mes</span>
    </div>
    <p class="text-xs {{ $subColor }} mb-5">
        hasta {{ $plan->maxProductsLabel() }} productos
    </p>

    <ul class="text-sm {{ $listColor }} space-y-2 mb-6 flex-1">
        @foreach($featuresMap[$plan->name] ?? [] as $feature)
        <li class="flex items-center gap-2">
            <i class="fas fa-check {{ $checkColor }} w-4"></i>
            {{ $feature }}
        </li>
        @endforeach
    </ul>

    {{ $slot }}

</div>
