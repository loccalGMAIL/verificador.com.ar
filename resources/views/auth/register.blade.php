@extends('layouts.public')

@section('title', 'Crear cuenta')

@section('content')
<div class="min-h-[calc(100vh-var(--site-nav-h,64px))] flex items-center justify-center px-4 py-12 bg-slate-50">
    <div class="w-full max-w-md">

        {{-- Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">

            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-slate-900">Crear cuenta</h2>
                <p class="text-slate-500 text-sm mt-1">
                    <span class="text-emerald-600 font-semibold">{{ config('app.trial_days') }} días gratis</span>,
                    sin tarjeta de crédito
                </p>
            </div>

            {{-- Errores globales --}}
            @if($errors->any())
                <div class="mb-5 bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg space-y-1">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            {{-- Google OAuth --}}
            <a href="{{ route('auth.google') }}"
               class="flex items-center justify-center gap-3 w-full border border-slate-300 rounded-lg
                      px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition mb-5">
                <svg class="w-4 h-4" viewBox="0 0 24 24" aria-hidden="true">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                Registrarse con Google
            </a>

            {{-- Separador --}}
            <div class="relative mb-5">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-slate-200"></div>
                </div>
                <div class="relative flex justify-center text-xs text-slate-400">
                    <span class="bg-white px-3">o con email y contraseña</span>
                </div>
            </div>

            {{-- Formulario --}}
            <form method="POST" action="{{ route('register') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="store_name" class="block text-sm font-medium text-slate-700 mb-1">
                        Nombre del comercio
                    </label>
                    <input id="store_name" name="store_name" type="text" required
                           value="{{ old('store_name') }}"
                           class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                  @error('store_name') border-red-400 @enderror"
                           placeholder="Ej: Supermercado Don Juan">
                </div>

                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700 mb-1">
                        Tu nombre
                    </label>
                    <input id="name" name="name" type="text" required
                           value="{{ old('name') }}"
                           class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                  @error('name') border-red-400 @enderror"
                           placeholder="Juan García">
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-1">
                        Email
                    </label>
                    <input id="email" name="email" type="email" required autocomplete="email"
                           value="{{ old('email') }}"
                           class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                  @error('email') border-red-400 @enderror"
                           placeholder="juan@micomercio.com">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700 mb-1">
                        Contraseña
                    </label>
                    <input id="password" name="password" type="password" required autocomplete="new-password"
                           class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <p class="text-xs text-slate-400 mt-1">Mínimo 8 caracteres</p>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1">
                        Confirmar contraseña
                    </label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required
                           class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <button type="submit"
                        class="w-full bg-emerald-500 text-white font-semibold py-2.5 rounded-lg
                               hover:bg-emerald-600 transition text-sm mt-2">
                    Crear cuenta y empezar prueba gratis
                </button>

                <p class="text-xs text-slate-400 text-center">
                    Al registrarte aceptás los
                    <a href="#" class="text-blue-500 hover:underline">Términos y condiciones</a>.
                </p>
            </form>

            {{-- Link a login --}}
            <p class="text-center text-sm text-slate-500 mt-5">
                ¿Ya tenés cuenta?
                <a href="{{ route('login') }}" class="text-blue-600 font-medium hover:underline">
                    Iniciá sesión
                </a>
            </p>
        </div>
    </div>
</div>
@endsection
