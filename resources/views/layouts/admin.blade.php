<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') &mdash; verificador.com.ar</title>

    {{-- Favicon --}}
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <style>[x-cloak] { display: none !important; }</style>

    @stack('styles')
</head>
<body class="bg-slate-100 text-slate-800" style="font-family: 'Inter', sans-serif;">

    <div class="flex h-screen overflow-hidden">

        {{-- ====== SIDEBAR ADMIN ====== --}}
        <aside id="sidebar"
               class="w-64 bg-slate-900 text-white flex flex-col flex-shrink-0 transition-transform duration-200 z-30
                      fixed inset-y-0 left-0 -translate-x-full
                      lg:static lg:translate-x-0">

            {{-- Logo + badge admin --}}
            <div class="flex items-center gap-2 px-5 py-5 border-b border-slate-800">
                <svg viewBox="0 0 36 36" class="w-7 h-7 flex-none" aria-hidden="true">
                    <circle cx="18" cy="18" r="14" fill="white" stroke="#2563eb" stroke-width="2.5"/>
                    <path d="M11 19 L16 24 L33 8" fill="none" stroke="#10b981" stroke-width="4"
                          stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <div>
                    <span class="font-bold text-sm leading-none block">verificador.com.ar</span>
                    <span class="text-[11px] text-slate-500 font-medium tracking-wide block mt-0.5">v{{ config('app.version') }}</span>
                </div>
            </div>

            {{-- Navegación --}}
            <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">
                @php $seg = request()->segment(2); @endphp

                <a href="{{ route('admin.home') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                          {{ $seg === null || $seg === '' ? 'bg-slate-700 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-gauge-high w-4 text-center"></i>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('admin.stores.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                          {{ $seg === 'stores' ? 'bg-slate-700 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-shop w-4 text-center"></i>
                    <span>Comercios</span>
                </a>

                <a href="{{ route('admin.users.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                          {{ $seg === 'users' ? 'bg-slate-700 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-users w-4 text-center"></i>
                    <span>Usuarios</span>
                </a>

                <a href="{{ route('admin.subscriptions.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                          {{ $seg === 'subscriptions' ? 'bg-slate-700 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-credit-card w-4 text-center"></i>
                    <span>Subscripciones</span>
                </a>

                <a href="{{ route('admin.plans.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                          {{ $seg === 'plans' ? 'bg-slate-700 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-list w-4 text-center"></i>
                    <span>Planes</span>
                </a>
            </nav>

            {{-- Logout --}}
            <div class="px-3 py-3 border-t border-slate-800">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="flex items-center gap-3 w-full px-3 py-2 rounded-lg text-sm font-medium
                                   text-slate-400 hover:bg-slate-800 hover:text-white transition">
                        <i class="fa-solid fa-arrow-right-from-bracket w-4 text-center"></i>
                        <span>Cerrar sesión</span>
                    </button>
                </form>
            </div>
        </aside>

        <div id="sidebar-overlay"
             class="fixed inset-0 bg-black/50 z-20 hidden lg:hidden"
             onclick="toggleSidebar()"></div>

        {{-- ====== CONTENIDO ====== --}}
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

            <header class="bg-white border-b border-slate-200 px-4 sm:px-6 py-3 flex items-center justify-between flex-shrink-0">
                <div class="flex items-center gap-3">
                    <button onclick="toggleSidebar()"
                            class="lg:hidden text-slate-500 hover:text-slate-800 p-1">
                        <i class="fa-solid fa-bars text-xl"></i>
                    </button>
                    <h1 class="text-base sm:text-lg font-semibold text-slate-800">
                        @yield('page-title', 'Admin')
                    </h1>
                </div>
                @auth
                <div x-data="{ open: {{ $errors->hasAny(['current_password', 'password']) ? 'true' : 'false' }} }">
                    <button @click="open = true"
                            class="text-sm text-slate-500 hover:text-slate-800 hidden sm:flex items-center gap-1.5 transition"
                            title="Cambiar mi contraseña">
                        {{ auth()->user()->name }}
                        <i class="fa-solid fa-lock text-[11px] text-slate-400"></i>
                    </button>

                    <div x-show="open" x-cloak
                         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
                         @keydown.escape.window="open = false">
                        <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6" @click.stop>
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-base font-semibold text-slate-800">Cambiar mi contraseña</h3>
                                <button @click="open = false" class="text-slate-400 hover:text-slate-600">
                                    <i class="fa-solid fa-xmark text-lg"></i>
                                </button>
                            </div>
                            <form method="POST" action="{{ route('admin.profile.password') }}">
                                @csrf @method('PUT')
                                <div class="space-y-4">
                                    @if(auth()->user()->password !== null)
                                    <div>
                                        <label class="block text-xs font-medium text-slate-600 mb-1">Contraseña actual</label>
                                        <input type="password" name="current_password" required autocomplete="current-password"
                                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                                        @error('current_password')
                                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    @else
                                    <div class="bg-amber-50 border border-amber-200 rounded-lg px-3 py-2 text-xs text-amber-700">
                                        <i class="fa-brands fa-google mr-1"></i>
                                        Tu cuenta usa Google OAuth. Podés establecer una contraseña local ahora.
                                    </div>
                                    @endif
                                    <div>
                                        <label class="block text-xs font-medium text-slate-600 mb-1">Nueva contraseña</label>
                                        <input type="password" name="password" required minlength="8" autocomplete="new-password"
                                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                                        @error('password')
                                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-slate-600 mb-1">Confirmar nueva contraseña</label>
                                        <input type="password" name="password_confirmation" required minlength="8" autocomplete="new-password"
                                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                                    </div>
                                </div>
                                <div class="flex justify-end gap-2 mt-5">
                                    <button type="button" @click="open = false"
                                            class="text-sm text-slate-600 px-4 py-2 rounded-lg border border-slate-200 hover:bg-slate-50 transition">
                                        Cancelar
                                    </button>
                                    <button type="submit"
                                            class="text-sm bg-blue-600 text-white font-medium px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                                        Guardar contraseña
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endauth
            </header>

            <main class="flex-1 overflow-y-auto p-4 sm:p-6">
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
                @if(session('warning'))
                    <div class="mb-4 bg-amber-50 border border-amber-200 text-amber-800 text-sm px-4 py-3 rounded-lg flex items-center gap-2">
                        <i class="fa-solid fa-triangle-exclamation text-amber-500"></i>
                        {{ session('warning') }}
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
