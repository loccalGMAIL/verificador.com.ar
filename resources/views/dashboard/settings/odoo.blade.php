@extends('layouts.app')

@section('title', 'Configuración')
@section('page-title', 'Configuración')

@section('content')

@php $settingsActiveTab = 'odoo'; @endphp
@include('dashboard.settings._tabs')

@if(session('success'))
<div class="mb-5 bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 text-sm text-emerald-700">
    <i class="fa-solid fa-circle-check mr-2"></i>{{ session('success') }}
</div>
@endif

@error('connection')
<div class="mb-5 bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-700">
    <i class="fa-solid fa-triangle-exclamation mr-2"></i>No se pudo conectar: {{ $message }}
</div>
@enderror

<div class="grid grid-cols-1 xl:grid-cols-2 gap-6 items-start">

    <div class="space-y-6">

        {{-- ── Estado de la conexión ─────────────────────────────── --}}
        @if($connection)
        <div class="bg-white rounded-xl border border-slate-200 p-6 space-y-4">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="font-semibold text-slate-800 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full {{ $connection->lastSyncFailed() ? 'bg-red-500' : 'bg-emerald-500' }}"></span>
                        Conectado a Odoo
                    </h3>
                    <p class="text-xs text-slate-500 mt-1 break-all">
                        {{ $connection->url }} · base <span class="font-mono">{{ $connection->database }}</span>
                    </p>
                </div>
                <form method="POST" action="{{ route('dashboard.settings.odoo.sync') }}">
                    @csrf
                    <button type="submit"
                            class="flex items-center gap-1.5 bg-violet-600 text-white px-4 py-2 rounded-lg text-sm font-semibold
                                   hover:bg-violet-700 transition whitespace-nowrap">
                        <i class="fa-solid fa-rotate text-xs"></i>
                        Sincronizar ahora
                    </button>
                </form>
            </div>

            <dl class="grid grid-cols-2 gap-3 text-sm">
                <div class="bg-slate-50 rounded-lg px-3 py-2">
                    <dt class="text-xs text-slate-500">Última sincronización</dt>
                    <dd class="font-medium text-slate-800">
                        {{ $connection->last_synced_at?->diffForHumans() ?? 'En curso / pendiente' }}
                    </dd>
                </div>
                <div class="bg-slate-50 rounded-lg px-3 py-2">
                    <dt class="text-xs text-slate-500">Productos desde Odoo</dt>
                    <dd class="font-medium text-slate-800">{{ number_format($odooProductCount, 0, ',', '.') }}</dd>
                </div>
            </dl>

            @if($connection->lastSyncFailed())
            <div class="bg-red-50 border border-red-200 rounded-lg px-3 py-2 text-sm text-red-700">
                <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                La última sincronización falló: {{ $connection->last_error }}
            </div>
            @elseif($stats = $connection->last_sync_stats)
            <p class="text-xs text-slate-500">
                Último resultado: {{ $stats['created'] ?? 0 }} nuevos ·
                {{ $stats['updated'] ?? 0 }} actualizados ·
                {{ $stats['deactivated'] ?? 0 }} desactivados
                @if(($stats['skipped_limit'] ?? 0) > 0)
                    · <span class="text-amber-600 font-medium">{{ $stats['skipped_limit'] }} sin importar por el límite de tu plan</span>
                @endif
                @if(($stats['skipped_conflict'] ?? 0) > 0)
                    · <span class="text-amber-600 font-medium">{{ $stats['skipped_conflict'] }} con código de barras duplicado</span>
                @endif
            </p>
            @endif

            <p class="text-xs text-slate-400">
                Los cambios de Odoo se traen automáticamente cada 30 minutos.
            </p>
        </div>
        @endif

        {{-- ── Formulario de conexión ────────────────────────────── --}}
        <form method="POST" action="{{ route('dashboard.settings.odoo.update') }}"
              class="bg-white rounded-xl border border-slate-200 p-6 space-y-5"
              x-data="{ url: @js(old('url', $connection?->url ?? '')) }">
            @csrf @method('PUT')

            <div>
                <h3 class="font-semibold text-slate-800 mb-1">
                    {{ $connection ? 'Datos de conexión' : 'Conectar con Odoo' }}
                </h3>
                <p class="text-xs text-slate-400">
                    Traé automáticamente nombre, código de barras y precio de venta de tus productos de Odoo 19 o superior.
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    URL de tu Odoo <span class="text-red-500">*</span>
                </label>
                <input type="text" name="url" x-model="url" required
                       placeholder="https://miempresa.odoo.com"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm
                              focus:outline-none focus:ring-2 focus:ring-blue-500
                              @error('url') border-red-400 @enderror">
                @error('url')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    Base de datos <span class="text-slate-400 font-normal text-xs">(opcional en Odoo Online)</span>
                </label>
                <input type="text" name="database" value="{{ old('database', $connection?->database) }}"
                       placeholder="Se completa con el subdominio de odoo.com"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm font-mono
                              focus:outline-none focus:ring-2 focus:ring-blue-500
                              @error('database') border-red-400 @enderror">
                @error('database')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                <p class="text-xs text-slate-400 mt-1">Solo hace falta si usás un dominio propio en lugar de <span class="font-mono">*.odoo.com</span>.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    API key @unless($connection)<span class="text-red-500">*</span>@endunless
                </label>
                <input type="password" name="api_key" autocomplete="off"
                       placeholder="{{ $connection ? $connection->maskedApiKey().' — dejá vacío para mantenerla' : 'Pegá la clave generada en Odoo' }}"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm font-mono
                              focus:outline-none focus:ring-2 focus:ring-blue-500
                              @error('api_key') border-red-400 @enderror">
                @error('api_key')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center gap-3 pt-1">
                <button type="submit"
                        class="bg-blue-600 text-white px-6 py-2.5 rounded-lg text-sm font-semibold hover:bg-blue-700 transition">
                    {{ $connection ? 'Probar y guardar' : 'Probar y conectar' }}
                </button>
            </div>
        </form>

        @if($connection)
        <form method="POST" action="{{ route('dashboard.settings.odoo.destroy') }}"
              onsubmit="return confirm('¿Desconectar Odoo? Los productos se conservan y vuelven a ser editables.')">
            @csrf @method('DELETE')
            <button type="submit" class="text-sm text-red-600 hover:text-red-800 transition">
                <i class="fa-solid fa-link-slash mr-1"></i>Desconectar Odoo
            </button>
        </form>
        @endif
    </div>

    {{-- ── Instrucciones ────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-slate-200 p-6 space-y-4 text-sm text-slate-600">
        <h3 class="font-semibold text-slate-800">¿Cómo obtengo la API key?</h3>
        <ol class="space-y-3 list-decimal list-inside">
            <li>
                En Odoo, activá el <strong>modo desarrollador</strong>: Ajustes → bajá hasta el final → <em>Activar el modo de desarrollador</em>.
            </li>
            <li>
                Abrí tu perfil (arriba a la derecha) → <strong>Mi perfil</strong> → pestaña <strong>Seguridad de la cuenta</strong>.
            </li>
            <li>
                En <strong>Claves API</strong> hacé clic en <em>Nueva clave API</em>, poné un nombre (ej. "Verificador")
                y elegí la duración más larga disponible.
            </li>
            <li>Copiá la clave y pegala acá. Odoo la muestra una sola vez.</li>
        </ol>

        <div class="bg-amber-50 border border-amber-200 rounded-lg px-3 py-2 text-xs text-amber-800 space-y-1">
            <p><i class="fa-solid fa-shield-halved mr-1"></i><strong>Seguridad:</strong> la clave tiene los mismos permisos que el usuario que la genera.</p>
            <p>
                Verificador solo lee productos. Si querés limitar el acceso, generala con un usuario
                que solo tenga permisos de lectura en Inventario o Ventas.
            </p>
        </div>

        <div class="text-xs text-slate-500 space-y-1">
            <p><strong>Qué se sincroniza:</strong> productos con código de barras — nombre, código, precio de venta y estado (archivados se desactivan).</p>
            <p>Los productos de Odoo se editan en Odoo; acá solo podés cambiarles la descripción y la imagen.</p>
        </div>
    </div>
</div>

@endsection
