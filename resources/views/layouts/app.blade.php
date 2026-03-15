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

    @stack('styles')
</head>
<body class="bg-slate-50 text-slate-800" style="font-family: 'Inter', sans-serif;">

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

                <a href="{{ route('dashboard.branches.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                          {{ $seg === 'branches' ? 'bg-blue-800 text-white' : 'text-blue-200 hover:bg-blue-900 hover:text-white' }}">
                    <i class="fa-solid fa-store w-4 text-center"></i>
                    <span>Sucursales</span>
                </a>

                <a href="{{ route('dashboard.subscription') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                          {{ $seg === 'subscription' ? 'bg-blue-800 text-white' : 'text-blue-200 hover:bg-blue-900 hover:text-white' }}">
                    <i class="fa-solid fa-credit-card w-4 text-center"></i>
                    <span>Subscripción</span>
                </a>

                <a href="{{ route('dashboard.settings') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                          {{ $seg === 'settings' ? 'bg-blue-800 text-white' : 'text-blue-200 hover:bg-blue-900 hover:text-white' }}">
                    <i class="fa-solid fa-gear w-4 text-center"></i>
                    <span>Configuración</span>
                </a>
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
</body>
</html>
