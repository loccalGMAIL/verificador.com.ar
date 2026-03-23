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
                    Probar gratis {{ config('app.trial_days') }} días
                </a>
            @endauth
        </div>
    </nav>

    {{-- Contenido principal --}}
    @yield('content')

    {{-- Footer --}}
    <footer class="py-12 border-t border-slate-100 text-center text-slate-400 text-sm">
        <p>&copy; {{ date('Y') }} Verificador.com.ar &mdash; Todos los derechos reservados.</p>
        <p class="mt-2 text-xs text-slate-300">
            Diseñado por
            <a href="https://pez.com.ar" target="_blank" rel="noopener"
               class="text-slate-400 hover:text-blue-500 transition font-medium">pez.com.ar</a>
        </p>
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

    {{-- Botón flotante WhatsApp --}}
    @php
        $waUrl = 'https://wa.me/543541549674?text=' . rawurlencode('¡Hola! Quiero obtener más información sobre verificador.com.ar.');
    @endphp
    <a href="{{ $waUrl }}"
       target="_blank"
       rel="noopener noreferrer"
       class="whatsapp-fab group"
       aria-label="Contactate por WhatsApp">
        <span class="whatsapp-fab__tooltip">Contactate con nosotros</span>
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
