<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') &mdash; verificador.com.ar</title>

    {{-- Favicon --}}
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    {{-- Tailwind CSS CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Font Awesome --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    {{-- Estilos propios --}}
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @stack('styles')
</head>
<body class="bg-slate-50 text-slate-800 overflow-hidden" style="font-family: 'Inter', sans-serif;">

    <div class="flex h-screen overflow-hidden">

        {{-- ====== SIDEBAR ====== --}}
        <aside id="sidebar"
               class="w-64 bg-blue-950 text-white flex flex-col flex-shrink-0 transition-transform duration-200 z-30
                      fixed inset-y-0 left-0 -translate-x-full
                      lg:static lg:translate-x-0">

            {{-- Logo --}}
            <div class="flex items-center gap-2 px-5 py-5 border-b border-blue-900">
                <svg viewBox="0 0 36 36" class="w-7 h-7 flex-none" aria-hidden="true">
                    <circle cx="18" cy="18" r="14" fill="white" stroke="#2563eb" stroke-width="2.5"/>
                    <path d="M11 19 L16 24 L33 8" fill="none" stroke="#10b981" stroke-width="4"
                          stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span class="font-bold text-lg leading-none">
                    verificador<span class="text-blue-400">.com.ar</span>
                </span>
            </div>

            {{-- Nombre del comercio --}}
            @auth
            <div class="px-5 py-3 border-b border-blue-900 text-sm text-blue-300 truncate">
                <span class="block text-xs text-blue-500 uppercase tracking-wide mb-0.5">Comercio</span>
                {{ auth()->user()->store->name ?? auth()->user()->name }}
            </div>
            @endauth

            {{-- Navegación principal --}}
            <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">
                @php $seg = request()->segment(2); @endphp

                <a href="{{ route('dashboard.home') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                          {{ $seg === null || $seg === '' ? 'bg-blue-800 text-white' : 'text-blue-200 hover:bg-blue-900 hover:text-white' }}">
                    <i class="fa-solid fa-gauge-high w-4 text-center"></i>
                    <span>Inicio</span>
                </a>

                <a href="{{ route('dashboard.products.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                          {{ $seg === 'products' ? 'bg-blue-800 text-white' : 'text-blue-200 hover:bg-blue-900 hover:text-white' }}">
                    <i class="fa-solid fa-box w-4 text-center"></i>
                    <span>Productos</span>
                </a>

                {{-- <a href="{{ route('dashboard.price-lists.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                          {{ $seg === 'price-lists' ? 'bg-blue-800 text-white' : 'text-blue-200 hover:bg-blue-900 hover:text-white' }}">
                    <i class="fa-solid fa-tags w-4 text-center"></i>
                    <span>Listas de precios</span>
                </a> --}}

                <a href="{{ route('dashboard.branches.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                          {{ $seg === 'branches' ? 'bg-blue-800 text-white' : 'text-blue-200 hover:bg-blue-900 hover:text-white' }}">
                    <i class="fa-solid fa-store w-4 text-center"></i>
                    <span>Sucursales</span>
                </a>

                <a href="{{ route('dashboard.statistics') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                          {{ $seg === 'estadisticas' ? 'bg-blue-800 text-white' : 'text-blue-200 hover:bg-blue-900 hover:text-white' }}">
                    <i class="fa-solid fa-chart-bar w-4 text-center"></i>
                    <span>Estadísticas Avanzadas</span>
                </a>

                {{-- <a href="{{ route('dashboard.subscription') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                          {{ $seg === 'subscription' ? 'bg-blue-800 text-white' : 'text-blue-200 hover:bg-blue-900 hover:text-white' }}">
                    <i class="fa-solid fa-credit-card w-4 text-center"></i>
                    <span>Subscripción</span>
                </a> --}}

                <a href="{{ route('dashboard.billing') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                          {{ $seg === 'billing' ? 'bg-blue-800 text-white' : 'text-blue-200 hover:bg-blue-900 hover:text-white' }}">
                    <i class="fa-solid fa-file-invoice-dollar w-4 text-center"></i>
                    <span>Estado de Cuenta</span>
                </a>

                {{-- Configuración expandible --}}
                <div x-data="{ open: {{ in_array($seg, ['settings', 'users']) ? 'true' : 'false' }} }">
                    <button @click="open = !open"
                            class="flex items-center gap-3 w-full px-3 py-2 rounded-lg text-sm font-medium transition
                                   {{ in_array($seg, ['settings', 'users']) ? 'bg-blue-800 text-white' : 'text-blue-200 hover:bg-blue-900 hover:text-white' }}">
                        <i class="fa-solid fa-gear w-4 text-center"></i>
                        <span class="flex-1 text-left">Configuración</span>
                        <i class="fa-solid fa-chevron-down text-xs transition-transform"
                           :class="open ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="open" class="mt-0.5 space-y-0.5 pl-3">
                        @php $currentTab = request('tab', 'general'); @endphp
                        @foreach([
                            'general'      => 'General',
                            'excel-import' => 'Importación Excel',
                            'print'        => 'Impresión QR',
                            'appearance'   => 'Apariencia',
                        ] as $tabKey => $tabLabel)
                        <a href="{{ route('dashboard.settings', ['tab' => $tabKey]) }}"
                           class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-medium transition
                                  {{ $seg === 'settings' && $currentTab === $tabKey
                                      ? 'bg-blue-700 text-white'
                                      : 'text-blue-300 hover:bg-blue-900 hover:text-white' }}">
                            <i class="fa-solid fa-minus w-3 text-center text-blue-500"></i>
                            {{ $tabLabel }}
                        </a>
                        @endforeach
                        <a href="{{ route('dashboard.users.index') }}"
                           class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-medium transition
                                  {{ $seg === 'users'
                                      ? 'bg-blue-700 text-white'
                                      : 'text-blue-300 hover:bg-blue-900 hover:text-white' }}">
                            <i class="fa-solid fa-minus w-3 text-center text-blue-500"></i>
                            Usuarios
                        </a>
                    </div>
                </div>
            </nav>

            {{-- Footer del sidebar --}}
            <div class="px-3 py-3 border-t border-blue-900">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="flex items-center gap-3 w-full px-3 py-2 rounded-lg text-sm font-medium
                                   text-blue-300 hover:bg-blue-900 hover:text-white transition">
                        <i class="fa-solid fa-arrow-right-from-bracket w-4 text-center"></i>
                        <span>Cerrar sesión</span>
                    </button>
                </form>
            </div>
        </aside>

        {{-- Overlay sidebar móvil --}}
        <div id="sidebar-overlay"
             class="fixed inset-0 bg-black/50 z-20 hidden lg:hidden"
             onclick="toggleSidebar()"></div>

        {{-- ====== CONTENIDO PRINCIPAL ====== --}}
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

            {{-- Topbar --}}
            <header class="bg-white border-b border-slate-200 px-4 sm:px-6 py-3 flex items-center justify-between flex-shrink-0">
                <div class="flex items-center gap-3">
                    {{-- Hamburger (móvil) --}}
                    <button onclick="toggleSidebar()"
                            class="lg:hidden text-slate-500 hover:text-slate-800 p-1">
                        <i class="fa-solid fa-bars text-xl"></i>
                    </button>
                    <h1 class="text-base sm:text-lg font-semibold text-slate-800">
                        @yield('page-title', 'Dashboard')
                    </h1>
                </div>

                {{-- Info usuario --}}
                @auth
                <div class="flex items-center gap-3">
                    @php $sub = auth()->user()->store?->subscription; @endphp
                    @if($sub && $sub->isOnTrial())
                        <span class="hidden sm:inline text-xs bg-amber-100 text-amber-700 font-medium px-2.5 py-1 rounded-full">
                            Trial: {{ $sub->trialDaysRemaining() }} día(s)
                        </span>
                    @endif
                    <span class="text-sm text-slate-600 font-medium hidden sm:inline">
                        {{ auth()->user()->name }}
                    </span>
                </div>
                @endauth
            </header>

            {{-- Banner de impersonación --}}
            @if(session('impersonating_admin_id'))
            <div class="bg-amber-400 text-amber-900 text-sm font-medium px-4 py-2 flex items-center justify-between gap-3 flex-shrink-0">
                <span>
                    <i class="fa-solid fa-user-secret mr-1.5"></i>
                    Navegando como <strong>{{ auth()->user()->name }}</strong>
                    ({{ auth()->user()->store->name ?? auth()->user()->email }})
                </span>
                <form method="POST" action="{{ route('impersonate.leave') }}">
                    @csrf
                    <button type="submit"
                            class="bg-amber-900 text-amber-50 text-xs font-semibold px-3 py-1 rounded-lg hover:bg-amber-800 transition">
                        <i class="fa-solid fa-arrow-left mr-1"></i> Volver al admin
                    </button>
                </form>
            </div>
            @endif

            {{-- Área de contenido con scroll --}}
            <main class="flex-1 overflow-y-auto p-4 sm:p-6">
                {{-- Flash messages --}}
                @if(session('success'))
                    <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm px-4 py-3 rounded-lg flex items-center gap-2">
                        <i class="fa-solid fa-circle-check text-emerald-500"></i>
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="mb-4 bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-lg flex items-center gap-2">
                        <i class="fa-solid fa-circle-exclamation text-red-500"></i>
                        {{ session('error') }}
                    </div>
                @endif

                {{-- Alerta global de suscripción expirada --}}
                @auth
                @php $layoutSub = auth()->user()->store?->subscription; @endphp
                @if($layoutSub?->isExpired())
                <div class="mb-4 bg-red-50 border border-red-200 rounded-xl px-4 py-3 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2 text-sm">
                        <i class="fa-solid fa-circle-exclamation text-red-500"></i>
                        <span class="text-red-800 font-medium">Tu suscripción expiró.</span>
                        <span class="text-red-700 hidden sm:inline">Elegí un plan para seguir usando el sistema.</span>
                    </div>
                    <a href="{{ route('dashboard.subscription') }}"
                       class="flex-shrink-0 bg-red-600 text-white text-xs font-semibold px-3 py-1.5 rounded-lg hover:bg-red-700 transition">
                        Ver planes
                    </a>
                </div>
                @elseif($layoutSub?->isOnTrial() && $layoutSub->trialDaysRemaining() <= 3)
                <div class="mb-4 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2 text-sm">
                        <i class="fa-solid fa-clock text-amber-500"></i>
                        <span class="text-amber-800 font-medium">
                            Te quedan <strong>{{ $layoutSub->trialDaysRemaining() }} día(s)</strong> de trial.
                        </span>
                        <span class="text-amber-700 hidden sm:inline">Elegí tu plan para no perder el acceso.</span>
                    </div>
                    <a href="{{ route('dashboard.subscription') }}"
                       class="flex-shrink-0 bg-amber-500 text-white text-xs font-semibold px-3 py-1.5 rounded-lg hover:bg-amber-600 transition">
                        Elegir plan
                    </a>
                </div>
                @endif
                @endauth

                {{-- Mensaje de límite alcanzado --}}
                @if(session('limit_reached'))
                <div class="mb-4 bg-violet-50 border border-violet-200 rounded-xl px-4 py-3 flex items-center gap-2 text-sm">
                    <i class="fa-solid fa-arrow-up text-violet-500"></i>
                    <span class="text-violet-800">{{ session('limit_reached') }}</span>
                    <a href="{{ route('dashboard.subscription') }}" class="ml-auto flex-shrink-0 text-violet-700 font-semibold underline text-xs">
                        Ver planes
                    </a>
                </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            var sidebar = document.getElementById('sidebar');
            var overlay = document.getElementById('sidebar-overlay');
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }
    </script>

    @stack('scripts')

    {{-- Botón flotante WhatsApp --}}
    @auth
    @php
        $waName  = auth()->user()->name;
        $waStore = auth()->user()->store->name ?? $waName;
        $waUrl   = 'https://wa.me/543541549674?text=' . rawurlencode("¡Hola! Soy {$waName} de {$waStore} y necesito ayuda con verificador.com.ar.");
    @endphp
    @else
    @php $waUrl = 'https://wa.me/543541549674'; @endphp
    @endauth
    <a href="{{ $waUrl }}"
       target="_blank"
       rel="noopener noreferrer"
       class="whatsapp-fab group"
       aria-label="Contactate por WhatsApp">
        <span class="whatsapp-fab__tooltip">¿Necesitás ayuda?</span>
        <i class="fa-brands fa-whatsapp whatsapp-fab__icon"></i>
    </a>

    <style>
        .whatsapp-fab {
            position: fixed;
            bottom: 1.5rem;
            right: 1.5rem;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 3.25rem;
            height: 3.25rem;
            background: #25D366;
            border-radius: 50%;
            box-shadow: 0 4px 16px rgba(37,211,102,.45);
            text-decoration: none;
            transition: transform .2s ease, box-shadow .2s ease;
        }
        .whatsapp-fab:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 22px rgba(37,211,102,.55);
        }
        .whatsapp-fab__icon {
            font-size: 1.75rem;
            color: #fff;
            line-height: 1;
        }
        .whatsapp-fab__tooltip {
            position: absolute;
            right: calc(100% + .75rem);
            background: #1a1a2e;
            color: #fff;
            font-size: .75rem;
            font-weight: 600;
            white-space: nowrap;
            padding: .35rem .75rem;
            border-radius: .5rem;
            opacity: 0;
            pointer-events: none;
            transform: translateX(6px);
            transition: opacity .2s ease, transform .2s ease;
        }
        .whatsapp-fab__tooltip::after {
            content: '';
            position: absolute;
            left: 100%;
            top: 50%;
            transform: translateY(-50%);
            border: 5px solid transparent;
            border-left-color: #1a1a2e;
        }
        .whatsapp-fab:hover .whatsapp-fab__tooltip {
            opacity: 1;
            transform: translateX(0);
        }
    </style>
</body>
</html>
