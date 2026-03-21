@extends('layouts.public')

@section('title', 'Verificador.com.ar | El precio correcto, siempre')

@section('content')

    {{-- ===================== HERO ===================== --}}
    <section class="relative overflow-hidden min-h-[460px] md:min-h-[520px] flex items-center">
        {{-- Imagen de fondo --}}
        <img
            src="{{ asset('Images/supermercado.jpeg') }}"
            alt="Supermercado"
            class="absolute inset-0 w-full h-full object-cover"
        >
        {{-- Degradado horizontal: oscuro a la izquierda, transparente a la derecha --}}
        <div class="absolute inset-0 bg-gradient-to-r from-blue-950/95 via-blue-950/60 to-transparent"></div>

        {{-- Contenido alineado a la izquierda --}}
        <div class="relative z-10 px-6 sm:px-10 py-14 md:py-20 max-w-2xl text-white reveal reveal-left">
            <h1 class="text-4xl md:text-5xl font-extrabold leading-tight mb-5">
                El precio correcto, siempre.
            </h1>
            <p class="text-base md:text-lg text-blue-100 mb-7 max-w-xl">
                Permite que tus clientes escaneen un QR y verifiquen el precio del producto en segundos.
            </p>
            <div class="flex flex-col sm:flex-row gap-4">
                <a href="{{ route('register') }}"
                   class="bg-emerald-500 hover:bg-emerald-600 text-white px-6 py-3 rounded-xl font-bold text-base md:text-lg transition">
                    Probar gratis {{ config('app.trial_days') }} días
                </a>

            </div>
        </div>
    </section>

    {{-- ===================== PROBLEMA ACTUAL ===================== --}}
    <section id="problema" class="py-10 px-6 bg-white reveal">
        <div class="max-w-4xl mx-auto">
            <h2 class="text-2xl md:text-3xl font-bold text-center mb-4">Problema Actual</h2>
            <img
                src="{{ asset('Images/problema.jpeg') }}"
                alt="Problema actual en comercios"
                class="w-full rounded-2xl shadow-md object-cover"
            >
        </div>
    </section>

    {{-- ===================== CÓMO FUNCIONA ===================== --}}
    <section id="como-funciona" class="py-12 px-6 bg-slate-50 reveal">
        <div class="max-w-5xl mx-auto">
            <h2 class="text-2xl md:text-3xl font-bold text-center mb-8">Cómo Funciona</h2>
            <div class="grid md:grid-cols-3 gap-6">

                <div class="text-center reveal reveal--delay-1">
                    <div class="relative mb-3">
                        <div class="rounded-2xl overflow-hidden shadow-md aspect-[4/3] ring-1 ring-slate-900/5">
                            <img
                                src="{{ asset('Images/image_e2a1fe85.png') }}"
                                alt="QR en góndola"
                                class="w-full h-full object-cover"
                            >
                        </div>
                        <div class="absolute top-3 left-3">
                            <div class="w-10 h-10 rounded-full bg-white/95 text-blue-700 font-extrabold flex items-center justify-center shadow-lg ring-1 ring-slate-900/10">
                                1
                            </div>
                        </div>
                    </div>
                    <p class="text-sm text-slate-600">El comercio coloca un QR en la góndola</p>
                </div>

                <div class="text-center reveal reveal--delay-2">
                    <div class="relative mb-3">
                        <div class="rounded-2xl overflow-hidden shadow-md aspect-[4/3] ring-1 ring-slate-900/5">
                            <img
                                src="{{ asset('Images/image_d0ff0168.png') }}"
                                alt="Cliente escaneando QR"
                                class="w-full h-full object-cover"
                            >
                        </div>
                        <div class="absolute top-3 left-3">
                            <div class="w-10 h-10 rounded-full bg-white/95 text-blue-700 font-extrabold flex items-center justify-center shadow-lg ring-1 ring-slate-900/10">
                                2
                            </div>
                        </div>
                    </div>
                    <p class="text-sm text-slate-600">El cliente lo escanea con su celular</p>
                </div>

                <div class="text-center reveal reveal--delay-3">
                    <div class="relative mb-3">
                        <div class="rounded-2xl overflow-hidden shadow-md aspect-[4/3] ring-1 ring-slate-900/5">
                            <img
                                src="{{ asset('Images/image_b76c1d1b.png') }}"
                                alt="Precio en pantalla"
                                class="w-full h-full object-cover"
                            >
                        </div>
                        <div class="absolute top-3 left-3">
                            <div class="w-10 h-10 rounded-full bg-white/95 text-blue-700 font-extrabold flex items-center justify-center shadow-lg ring-1 ring-slate-900/10">
                                3
                            </div>
                        </div>
                    </div>
                    <p class="text-sm text-slate-600">La cámara reconoce el producto y muestra el precio actualizado</p>
                </div>

            </div>
        </div>
    </section>


    {{-- ===================== BENEFICIOS PARA EL NEGOCIO ===================== --}}
    <section class="py-12 px-6 bg-slate-50 reveal">
        <div class="max-w-5xl mx-auto">
            <h2 class="text-2xl md:text-3xl font-bold text-center mb-8">Beneficios para el Negocio</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6 text-center">
                <div class="reveal reveal--delay-1 rounded-2xl bg-white/80 border border-slate-200 p-4 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition">
                    <div class="w-14 h-14 md:w-16 md:h-16 bg-gradient-to-br from-blue-100 to-emerald-50 rounded-2xl flex items-center justify-center mx-auto mb-3 ring-1 ring-slate-900/5">
                        <i class="fas fa-comment-slash text-blue-700 text-2xl"></i>
                    </div>
                    <p class="text-sm font-semibold text-slate-800 leading-snug">Reduce reclamos en caja</p>
                </div>
                <div class="reveal reveal--delay-2 rounded-2xl bg-white/80 border border-slate-200 p-4 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition">
                    <div class="w-14 h-14 md:w-16 md:h-16 bg-gradient-to-br from-blue-100 to-emerald-50 rounded-2xl flex items-center justify-center mx-auto mb-3 ring-1 ring-slate-900/5">
                        <i class="fas fa-smile text-blue-700 text-2xl"></i>
                    </div>
                    <p class="text-sm font-semibold text-slate-800 leading-snug">Mejora la experiencia del cliente</p>
                </div>
                <div class="reveal reveal--delay-3 rounded-2xl bg-white/80 border border-slate-200 p-4 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition">
                    <div class="w-14 h-14 md:w-16 md:h-16 bg-gradient-to-br from-blue-100 to-emerald-50 rounded-2xl flex items-center justify-center mx-auto mb-3 ring-1 ring-slate-900/5">
                        <i class="fas fa-store text-blue-700 text-2xl"></i>
                    </div>
                    <p class="text-sm font-semibold text-slate-800 leading-snug">Moderniza tu comercio</p>
                </div>
                <div class="reveal reveal--delay-4 rounded-2xl bg-white/80 border border-slate-200 p-4 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition">
                    <div class="w-14 h-14 md:w-16 md:h-16 bg-gradient-to-br from-blue-100 to-emerald-50 rounded-2xl flex items-center justify-center mx-auto mb-3 ring-1 ring-slate-900/5">
                        <i class="fas fa-bolt text-blue-700 text-2xl"></i>
                    </div>
                    <p class="text-sm font-semibold text-slate-800 leading-snug">Implementación en minutos</p>
                </div>
                <div class="reveal reveal--delay-1 rounded-2xl bg-white/80 border border-slate-200 p-4 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition">
                    <div class="w-14 h-14 md:w-16 md:h-16 bg-gradient-to-br from-blue-100 to-emerald-50 rounded-2xl flex items-center justify-center mx-auto mb-3 ring-1 ring-slate-900/5">
                        <i class="fas fa-mobile-alt text-blue-700 text-2xl"></i>
                    </div>
                    <p class="text-sm font-semibold text-slate-800 leading-snug">No requiere apps del cliente</p>
                </div>
                <div class="reveal reveal--delay-2 rounded-2xl bg-white/80 border border-slate-200 p-4 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition">
                    <div class="w-14 h-14 md:w-16 md:h-16 bg-gradient-to-br from-blue-100 to-emerald-50 rounded-2xl flex items-center justify-center mx-auto mb-3 ring-1 ring-slate-900/5">
                        <i class="fas fa-tag text-blue-700 text-2xl"></i>
                    </div>
                    <p class="text-sm font-semibold text-slate-800 leading-snug">Precios correctos siempre</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== COMPARACIÓN DE COSTOS ===================== --}}
    <section id="costos" class="py-10 px-6 bg-white reveal">
        <div class="max-w-4xl mx-auto">
            <h2 class="text-xl md:text-2xl font-bold text-center mb-3">Comparación de Costos</h2>
            <p class="text-center text-slate-500 text-sm md:text-base mb-7">La competencia cobra más de U$S 850 por un verificador físico. Nosotros, mucho menos.</p>

            <div class="grid md:grid-cols-2 gap-5 items-center">

                {{-- Tarjeta estilo MercadoLibre desenfocada --}}
                <div class="relative reveal reveal-left">
                    <div class="blur-sm pointer-events-none select-none rounded-2xl border border-slate-200 shadow-md overflow-hidden bg-white p-5">
                        {{-- Header ML --}}
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-6 h-6 rounded-full bg-yellow-400"></div>
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Mercado Libre</span>
                        </div>
                        {{-- Imagen del producto --}}
                        <div class="bg-slate-100 rounded-xl h-32 flex items-center justify-center mb-4 overflow-hidden">
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
                        <div class="bg-red-500/90 text-white font-bold text-base px-5 py-3 rounded-2xl shadow-xl rotate-[-3deg]">
                            Verificador físico
                            = U$S 850
                        </div>
                    </div>
                </div>

                {{-- Verificador.com.ar --}}
                <div class="rounded-2xl border-2 border-blue-600 shadow-xl p-6 text-center bg-blue-50 reveal reveal-right reveal--delay-1">
                    <div class="w-12 h-12 bg-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-check-circle text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-extrabold text-blue-700 mb-2">verificador.com.ar</h3>
                    <p class="text-slate-500 text-sm mb-5">Sin hardware. Sin instalación. Desde cualquier dispositivo.</p>
                    <div class="text-4xl font-extrabold text-blue-700 mb-1">$5 <span class="text-base font-normal text-slate-400">USD / mes</span></div>
                    <p class="text-xs text-slate-400 mb-5">≈ menos de lo que cuesta un café por día</p>
                    <ul class="text-sm text-slate-600 space-y-2 text-left mb-5">
                        <li><i class="fas fa-check text-emerald-500 mr-2"></i>QR ilimitados</li>
                        <li><i class="fas fa-check text-emerald-500 mr-2"></i>Precios siempre actualizados</li>
                        <li><i class="fas fa-check text-emerald-500 mr-2"></i>Sin costo de instalación</li>
                    </ul>
                    <a href="{{ route('register') }}"
                       class="w-full block bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 rounded-xl transition text-center">
                        Probar gratis {{ config('app.trial_days') }} días
                    </a>
                </div>

            </div>
        </div>
    </section>

    {{-- ===================== PLANES ===================== --}}
    <section id="precios" class="py-10 px-6 bg-slate-50 reveal">
        <div class="max-w-6xl mx-auto">
            <h2 class="text-xl md:text-2xl font-bold text-center mb-2">Planes de suscripción</h2>
            <p class="text-center text-slate-500 text-sm md:text-base mb-7">Precios en pesos argentinos, sin costos ocultos</p>

            <div class="grid md:grid-cols-{{ $plans->count() ?: 4 }} gap-4 items-start">

                @foreach($plans as $plan)
                <x-plan-card
                    :plan="$plan"
                    :featured="$plan->featured"
                    variant="landing"
                    class="{{ $loop->first ? 'reveal reveal-left' : ($loop->last ? 'reveal reveal-right reveal--delay-3' : 'reveal reveal--delay-' . $loop->index) }}">
                    <a href="{{ route('register') }}"
                       class="w-full block py-2.5 rounded-xl text-sm font-bold transition text-center
                              {{ $plan->featured
                                  ? 'bg-white text-blue-600 hover:bg-blue-50'
                                  : 'border border-blue-600 text-blue-600 hover:bg-blue-50' }}">
                        Elegir {{ $plan->name }}
                    </a>
                </x-plan-card>
                @endforeach

            </div>
        </div>
    </section>

    {{-- ===================== FACILIDAD DE USO ===================== --}}
    {{-- <section class="py-16 px-6 bg-white">
        <div class="max-w-5xl mx-auto">
            <h2 class="text-3xl font-bold text-center mb-12">Facilidad de Uso</h2>
            <div class="grid md:grid-cols-2 gap-10 items-center">

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
    </section> --}}

    {{-- ===================== CTA FINAL ===================== --}}
    <section class="py-16 px-6 text-center max-w-3xl mx-auto reveal">
        <h2 class="text-3xl md:text-4xl font-extrabold mb-3">Empieza a modernizar tu comercio hoy.</h2>
        <p class="text-slate-500 text-base md:text-lg mb-8">Únete a cientos de comercios que ya optimizaron sus ventas.</p>
        <a href="{{ route('register') }}"
           class="inline-block bg-emerald-500 hover:bg-emerald-600 text-white px-8 py-4 rounded-2xl font-bold text-lg shadow-xl transition">
            Probar gratis {{ config('app.trial_days') }} días
        </a>
    </section>

@endsection
