<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name'))</title>

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
    <nav class="sticky top-0 z-50 bg-white/90 backdrop-blur-sm border-b border-slate-100 flex items-center justify-between px-6 py-4 max-w-full">
        <a href="{{ url('/') }}" class="text-2xl font-bold text-blue-600">
            verificador<span class="text-slate-400">.com.ar</span>
        </a>
        <div class="hidden md:flex space-x-8 font-medium">
            <a href="#problema" class="hover:text-blue-600 transition">Problema</a>
            <a href="#como-funciona" class="hover:text-blue-600 transition">Cómo funciona</a>
            <a href="#costos" class="hover:text-blue-600 transition">Costos</a>
            <a href="#precios" class="hover:text-blue-600 transition">Precios</a>
        </div>
        <a href="#" class="bg-blue-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-blue-700 transition">
            Probar Gratis
        </a>
    </nav>

    {{-- Contenido principal --}}
    @yield('content')

    {{-- Footer --}}
    <footer class="py-12 border-t border-slate-100 text-center text-slate-400 text-sm">
        &copy; {{ date('Y') }} Verificador.com.ar &mdash; Todos los derechos reservados.
    </footer>

    @stack('scripts')
</body>
</html>
