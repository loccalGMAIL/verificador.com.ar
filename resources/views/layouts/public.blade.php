<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name'))</title>

    {{-- Favicon --}}
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    {{-- Tailwind CSS CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Font Awesome --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

    {{-- Estilos propios --}}
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    @stack('styles')
</head>
<body class="text-slate-800 bg-white">

    {{-- Navegación --}}
    <nav id="site-nav" class="sticky top-0 z-50 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-6 py-3 max-w-full">
        <a href="{{ url('/') }}" class="text-xl font-bold text-blue-600 flex items-center gap-2">
            <svg viewBox="0 0 36 36" class="w-7 h-7 flex-none" aria-hidden="true" focusable="false">
                <circle cx="18" cy="18" r="14" fill="white" stroke="#2563eb" stroke-width="2.5"></circle>
                <path d="M11 19 L16 24 L33 8" fill="none" stroke="#10b981" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
            <span>verificador<span class="text-slate-400">.com.ar</span></span>
        </a>
        <div class="hidden md:flex space-x-6 font-medium text-sm">
            <a href="{{ url('/') }}#problema" class="hover:text-blue-600 transition">Problema</a>
            <a href="{{ url('/') }}#como-funciona" class="hover:text-blue-600 transition">Cómo funciona</a>
            <a href="{{ url('/') }}#costos" class="hover:text-blue-600 transition">Comparación de Costos</a>
            <a href="{{ url('/') }}#precios" class="hover:text-blue-600 transition">Precios</a>
        </div>

        <div class="flex items-center gap-3">
            @auth
                <a href="{{ auth()->user()->isAdmin() ? route('admin.home') : route('dashboard.home') }}"
                   class="bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold text-sm hover:bg-blue-700 transition flex items-center gap-2">
                    <i class="fa-solid fa-gauge-high text-xs"></i>
                    Ir al panel
                </a>
            @else
                <a href="{{ route('login') }}"
                   class="hidden sm:inline text-slate-600 hover:text-blue-600 font-medium text-sm transition">
                    Iniciar sesión
                </a>
                <a href="{{ route('register') }}"
                   class="bg-emerald-500 text-white px-4 py-2 rounded-lg font-semibold text-sm hover:bg-emerald-600 transition">
                    Probar gratis 7 días
                </a>
            @endauth
        </div>
    </nav>

    {{-- Contenido principal --}}
    @yield('content')

    {{-- Footer --}}
    <footer class="py-12 border-t border-slate-100 text-center text-slate-400 text-sm">
        &copy; {{ date('Y') }} Verificador.com.ar &mdash; Todos los derechos reservados.
    </footer>

    <script>
        (function () {
            var root = document.documentElement;
            var nav = document.getElementById('site-nav');
            if (!nav) return;

            var raf = null;
            function applyNavHeight() {
                raf = null;
                var h = Math.ceil(nav.getBoundingClientRect().height);
                if (h) root.style.setProperty('--site-nav-h', h + 'px');
            }

            function schedule() {
                if (raf) cancelAnimationFrame(raf);
                raf = requestAnimationFrame(applyNavHeight);
            }

            window.addEventListener('resize', schedule, { passive: true });

            if (document.fonts && document.fonts.ready) {
                document.fonts.ready.then(schedule);
            }

            schedule();
            setTimeout(schedule, 250);
        })();
    </script>

    <script>
        (function () {
            var els = document.querySelectorAll('.reveal');
            if (!els.length) return;

            if (!('IntersectionObserver' in window)) {
                els.forEach(function (el) { el.classList.add('is-visible'); });
                return;
            }

            var io = new IntersectionObserver(function (entries) {
                entries.forEach(function (e) {
                    if (e.isIntersecting) {
                        e.target.classList.add('is-visible');
                        io.unobserve(e.target);
                    }
                });
            }, { threshold: 0.12 });

            els.forEach(function (el) { io.observe(el); });
        })();
    </script>

    @stack('scripts')
</body>
</html>
