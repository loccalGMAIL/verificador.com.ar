@extends('layouts.public')

@section('title', 'Verificador.com.ar | El precio correcto, siempre')

@section('content')

    {{-- ===================== HERO ===================== --}}
    <section class="relative overflow-hidden min-h-[520px] flex items-center">
        {{-- Imagen de fondo --}}
        <img
            src="{{ asset('Images/supermercado.jpeg') }}"
            alt="Supermercado"
            class="absolute inset-0 w-full h-full object-cover"
        >
        {{-- Degradado horizontal: oscuro a la izquierda, transparente a la derecha --}}
        <div class="absolute inset-0 bg-gradient-to-r from-blue-950/95 via-blue-950/60 to-transparent"></div>

        {{-- Contenido alineado a la izquierda --}}
        <div class="relative z-10 px-12 py-20 max-w-2xl text-white">
            <h1 class="text-5xl md:text-6xl font-extrabold leading-tight mb-6">
                El precio correcto, siempre.
            </h1>
            <p class="text-lg text-blue-100 mb-8 max-w-xl">
                Permite que tus clientes escaneen un QR y verifiquen el precio del producto en segundos.
            </p>
            <div class="flex flex-col sm:flex-row gap-4">
                <button class="bg-emerald-500 hover:bg-emerald-600 text-white px-8 py-4 rounded-xl font-bold text-lg transition">
                    Probar gratis 7 días
                </button>
                <button class="bg-white/10 border border-white/40 hover:bg-white/20 text-white px-8 py-4 rounded-xl font-bold text-lg backdrop-blur-sm transition">
                    Ver demo
                </button>
            </div>
        </div>
    </section>

    {{-- ===================== PROBLEMA ACTUAL ===================== --}}
    <section id="problema" class="py-8 px-6 bg-white scroll-mt-16">
        <div class="max-w-4xl mx-auto">
            <h2 class="text-3xl font-bold text-center mb-4">Problema Actual</h2>
            <img
                src="{{ asset('Images/problema.jpeg') }}"
                alt="Problema actual en comercios"
                class="w-full rounded-2xl shadow-md object-cover"
            >
        </div>
    </section>

    {{-- ===================== CÓMO FUNCIONA ===================== --}}
    <section id="como-funciona" class="py-16 px-6 bg-slate-50">
        <div class="max-w-5xl mx-auto">
            <h2 class="text-3xl font-bold text-center mb-12">Cómo Funciona</h2>
            <div class="grid md:grid-cols-3 gap-8">

                <div class="text-center">
                    <div class="relative mb-4 rounded-2xl overflow-hidden shadow-md aspect-[4/3]">
                        <img
                            src="{{ asset('Images/image_e2a1fe85.png') }}"
                            alt="QR en góndola"
                            class="w-full h-full object-cover"
                        >
                        <span class="absolute top-3 left-3 bg-blue-600 text-white text-xs font-bold w-6 h-6 rounded-full flex items-center justify-center">1</span>
                    </div>
                    <p class="text-sm text-slate-600">El comercio coloca un QR en la góndola</p>
                </div>

                <div class="text-center">
                    <div class="relative mb-4 rounded-2xl overflow-hidden shadow-md aspect-[4/3]">
                        <img
                            src="{{ asset('Images/image_d0ff0168.png') }}"
                            alt="Cliente escaneando QR"
                            class="w-full h-full object-cover"
                        >
                        <span class="absolute top-3 left-3 bg-blue-600 text-white text-xs font-bold w-6 h-6 rounded-full flex items-center justify-center">2</span>
                    </div>
                    <p class="text-sm text-slate-600">El cliente lo escanea con su celular</p>
                </div>

                <div class="text-center">
                    <div class="relative mb-4 rounded-2xl overflow-hidden shadow-md aspect-[4/3]">
                        <img
                            src="{{ asset('Images/image_b76c1d1b.png') }}"
                            alt="Precio en pantalla"
                            class="w-full h-full object-cover"
                        >
                        <span class="absolute top-3 left-3 bg-blue-600 text-white text-xs font-bold w-6 h-6 rounded-full flex items-center justify-center">3</span>
                    </div>
                    <p class="text-sm text-slate-600">La cámara reconoce el producto y muestra el precio actualizado</p>
                </div>

            </div>
        </div>
    </section>


    {{-- ===================== BENEFICIOS PARA EL NEGOCIO ===================== --}}
    <section class="py-16 px-6 bg-slate-50">
        <div class="max-w-5xl mx-auto">
            <h2 class="text-3xl font-bold text-center mb-12">Beneficios para el Negocio</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6 text-center">
                <div>
                    <div class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-comment-slash text-blue-600 text-xl"></i>
                    </div>
                    <p class="text-sm font-semibold text-slate-700">Reduce reclamos en caja</p>
                </div>
                <div>
                    <div class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-smile text-blue-600 text-xl"></i>
                    </div>
                    <p class="text-sm font-semibold text-slate-700">Mejora la experiencia del cliente</p>
                </div>
                <div>
                    <div class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-store text-blue-600 text-xl"></i>
                    </div>
                    <p class="text-sm font-semibold text-slate-700">Moderniza tu comercio</p>
                </div>
                <div>
                    <div class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-bolt text-blue-600 text-xl"></i>
                    </div>
                    <p class="text-sm font-semibold text-slate-700">Implementación en minutos</p>
                </div>
                <div>
                    <div class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-mobile-alt text-blue-600 text-xl"></i>
                    </div>
                    <p class="text-sm font-semibold text-slate-700">No requiere apps del cliente</p>
                </div>
                <div>
                    <div class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-tag text-blue-600 text-xl"></i>
                    </div>
                    <p class="text-sm font-semibold text-slate-700">Precios correctos siempre</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== COMPARACIÓN DE COSTOS ===================== --}}
    <section id="costos" class="py-16 px-6 bg-white scroll-mt-16">
        <div class="max-w-4xl mx-auto">
            <h2 class="text-3xl font-bold text-center mb-4">Comparación de Costos</h2>
            <p class="text-center text-slate-500 mb-10">La competencia cobra más de U$S 850 por un verificador físico. Nosotros, mucho menos.</p>

            <div class="grid md:grid-cols-2 gap-8 items-center">

                {{-- Tarjeta estilo MercadoLibre desenfocada --}}
                <div class="relative">
                    <div class="blur-sm pointer-events-none select-none rounded-2xl border border-slate-200 shadow-md overflow-hidden bg-white p-5">
                        {{-- Header ML --}}
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-6 h-6 rounded-full bg-yellow-400"></div>
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Mercado Libre</span>
                        </div>
                        {{-- Imagen del producto --}}
                        <div class="bg-slate-100 rounded-xl h-36 flex items-center justify-center mb-4 overflow-hidden">
                            <img src="https://http2.mlstatic.com/D_Q_NP_2X_750685-MLA85297196307_052025-E.webp" alt="Verificador físico" class="h-full object-contain">
                        </div>
                        {{-- Info --}}
                        <p class="text-sm text-slate-500 mb-1">Verificador de precios electrónico para comercios</p>
                        <p class="text-2xl font-extrabold text-slate-800 mb-1">$1.149.990</p>
                        <p class="text-xs text-green-600 mb-3">12x $70.832 sin interés</p>
                        <div class="w-full bg-blue-500 text-white text-center text-sm font-bold py-2 rounded-xl">
                            Comprar ahora
                        </div>
                        <p class="text-xs text-slate-400 mt-3 text-center">Envío gratis · Vendido por TiendaTech</p>
                    </div>
                    {{-- Etiqueta encima --}}
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="bg-red-500/90 text-white font-bold text-lg px-6 py-3 rounded-2xl shadow-xl rotate-[-3deg]">
                            Verificador físico 
                            = U$S 850
                        </div>
                    </div>
                </div>

                {{-- Verificador.com.ar --}}
                <div class="rounded-2xl border-2 border-blue-600 shadow-xl p-8 text-center bg-blue-50">
                    <div class="w-14 h-14 bg-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-check-circle text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-extrabold text-blue-700 mb-2">verificador.com.ar</h3>
                    <p class="text-slate-500 text-sm mb-6">Sin hardware. Sin instalación. Desde cualquier dispositivo.</p>
                    <div class="text-5xl font-extrabold text-blue-700 mb-1">$5 <span class="text-lg font-normal text-slate-400">USD / mes</span></div>
                    <p class="text-xs text-slate-400 mb-6">≈ menos de lo que cuesta un café por día</p>
                    <ul class="text-sm text-slate-600 space-y-2 text-left mb-6">
                        <li><i class="fas fa-check text-emerald-500 mr-2"></i>QR ilimitados</li>
                        <li><i class="fas fa-check text-emerald-500 mr-2"></i>Precios siempre actualizados</li>
                        <li><i class="fas fa-check text-emerald-500 mr-2"></i>Sin costo de instalación</li>
                    </ul>
                    <button class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl transition">
                        Probar gratis 7 días
                    </button>
                </div>

            </div>
        </div>
    </section>

    {{-- ===================== PLANES ===================== --}}
    <section id="precios" class="py-16 px-6 bg-slate-50 scroll-mt-16">
        <div class="max-w-5xl mx-auto">
            <h2 class="text-3xl font-bold text-center mb-2">💳 Planes de suscripción</h2>
            <p class="text-center text-slate-500 mb-10">⭐ Standard es el plan recomendado</p>

            <div class="overflow-x-auto rounded-2xl border border-slate-200 shadow-sm">
                <table class="w-full text-sm border-collapse">
                    {{-- Encabezado --}}
                    <thead>
                        <tr>
                            <th class="py-4 px-5 bg-slate-50 text-left text-slate-500 font-semibold border-b border-slate-200 w-44">Feature</th>
                            <th class="py-4 px-5 bg-slate-50 text-center text-slate-700 font-bold border-b border-slate-200">Basic</th>
                            <th class="py-4 px-5 bg-blue-600 text-center text-white font-bold border-b border-blue-500">Standard ⭐</th>
                            <th class="py-4 px-5 bg-slate-50 text-center text-slate-700 font-bold border-b border-slate-200">Pro</th>
                            <th class="py-4 px-5 bg-slate-50 text-center text-slate-700 font-bold border-b border-slate-200">Business</th>
                        </tr>
                        <tr>
                            <td class="py-3 px-5 bg-slate-50 border-b border-slate-200 text-slate-400 text-xs">Precio</td>
                            <td class="py-3 px-5 bg-slate-50 border-b border-slate-200 text-center font-extrabold text-slate-800">$5 <span class="text-xs font-normal text-slate-400">/mes</span></td>
                            <td class="py-3 px-5 bg-blue-600 border-b border-blue-500 text-center font-extrabold text-white">$10 <span class="text-xs font-normal text-blue-200">/mes</span></td>
                            <td class="py-3 px-5 bg-slate-50 border-b border-slate-200 text-center font-extrabold text-slate-800">$20 <span class="text-xs font-normal text-slate-400">/mes</span></td>
                            <td class="py-3 px-5 bg-slate-50 border-b border-slate-200 text-center font-extrabold text-slate-800">$30 <span class="text-xs font-normal text-slate-400">/mes</span></td>
                        </tr>
                    </thead>
                    {{-- Filas --}}
                    <tbody>
                        @php
                            $check = '<i class="fas fa-check text-emerald-500"></i>';
                            $dash  = '<span class="text-slate-300">&mdash;</span>';
                            $rows  = [
                                ['Productos',                   'hasta 2.000', 'hasta 5.000',  'hasta 15.000', 'ilimitados'],
                                ['Subida de Excel',             $check,        $check,          $check,         $check],
                                ['Consulta por escaneo',        $check,        $check,          $check,         $check],
                                ['QR del comercio',             $check,        $check,          $check,         $check],
                                ['Búsqueda manual',             $dash,         $check,          $check,         $check],
                                ['Historial de importaciones',  $dash,         $check,          $check,         $check],
                                ['Estadísticas de escaneo',     $dash,         'básicas',       'avanzadas',    'avanzadas'],
                                ['Listas de precios',           $dash,         $dash,           $check,         $check],
                                ['Sucursales',                  $dash,         $dash,           $dash,          $check],
                                ['API de integración',          $dash,         $dash,           $dash,          $check],
                                ['Soporte',                     'básico',      'estándar',      'prioritario',  'prioritario'],
                            ];
                        @endphp
                        @foreach($rows as $i => $row)
                        <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-slate-50/50' }}">
                            <td class="py-3 px-5 text-slate-600 font-medium border-b border-slate-100">{{ $row[0] }}</td>
                            <td class="py-3 px-5 text-center border-b border-slate-100">{!! $row[1] !!}</td>
                            <td class="py-3 px-5 text-center border-b border-blue-100 bg-blue-50 font-medium text-blue-700">{!! $row[2] !!}</td>
                            <td class="py-3 px-5 text-center border-b border-slate-100">{!! $row[3] !!}</td>
                            <td class="py-3 px-5 text-center border-b border-slate-100">{!! $row[4] !!}</td>
                        </tr>
                        @endforeach
                        {{-- Fila de botones --}}
                        <tr>
                            <td class="py-4 px-5"></td>
                            <td class="py-4 px-5 text-center">
                                <button class="px-4 py-2 border border-blue-600 text-blue-600 rounded-lg text-xs font-bold hover:bg-blue-50 transition">Elegir</button>
                            </td>
                            <td class="py-4 px-5 text-center bg-blue-50">
                                <button class="px-4 py-2 bg-blue-600 text-white rounded-lg text-xs font-bold hover:bg-blue-700 transition">Elegir Standard</button>
                            </td>
                            <td class="py-4 px-5 text-center">
                                <button class="px-4 py-2 border border-blue-600 text-blue-600 rounded-lg text-xs font-bold hover:bg-blue-50 transition">Elegir</button>
                            </td>
                            <td class="py-4 px-5 text-center">
                                <button class="px-4 py-2 border border-blue-600 text-blue-600 rounded-lg text-xs font-bold hover:bg-blue-50 transition">Elegir</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    {{-- ===================== FACILIDAD DE USO ===================== --}}
    <section class="py-16 px-6 bg-white">
        <div class="max-w-5xl mx-auto">
            <h2 class="text-3xl font-bold text-center mb-12">Facilidad de Uso</h2>
            <div class="grid md:grid-cols-2 gap-10 items-center">
                {{-- Captura del panel --}}
                <div class="rounded-2xl overflow-hidden shadow-xl border border-slate-200">
                    <div class="bg-slate-700 flex items-center gap-1.5 px-4 py-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-400"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-yellow-400"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-green-400"></span>
                        <span class="ml-3 text-xs text-slate-300">Panel de control – verificador.com.ar</span>
                    </div>
                    <img
                        src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&q=80&w=800"
                        alt="Panel de control"
                        class="w-full object-cover"
                    >
                </div>
                {{-- Texto --}}
                <div>
                    <h3 class="text-2xl font-bold mb-4">Panel de control simple</h3>
                    <p class="text-slate-500 text-lg">Configuralo en menos de 10 minutos.</p>
                    <ul class="mt-6 space-y-3 text-slate-600">
                        <li class="flex items-center gap-3">
                            <i class="fas fa-check-circle text-emerald-500"></i>
                            <span>Cargá y actualizá precios fácilmente</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <i class="fas fa-check-circle text-emerald-500"></i>
                            <span>Generá QR por producto o categoría</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <i class="fas fa-check-circle text-emerald-500"></i>
                            <span>Visualizá estadísticas de escaneos</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== CTA FINAL ===================== --}}
    <section class="py-24 px-6 text-center max-w-3xl mx-auto">
        <h2 class="text-4xl font-extrabold mb-4">Empieza a modernizar tu comercio hoy.</h2>
        <p class="text-slate-500 text-lg mb-10">Únete a cientos de comercios que ya optimizaron sus ventas.</p>
        <button class="bg-emerald-500 hover:bg-emerald-600 text-white px-10 py-5 rounded-2xl font-bold text-xl shadow-xl transition">
            Probar gratis 7 días
        </button>
        <p class="mt-4 text-slate-400 text-sm">No requiere tarjeta de crédito</p>
    </section>

@endsection
