@extends('layouts.admin')

@section('title', 'Comercios')
@section('page-title', 'Comercios')

@section('content')

{{-- Modal de confirmación de eliminación (único, compartido) --}}
<div x-data="{
        open: false,
        storeName: '',
        storeAction: '',
        confirmName: '',
        launch(name, action) {
            this.storeName   = name;
            this.storeAction = action;
            this.confirmName = '';
            this.open        = true;
        }
     }"
     @keydown.escape.window="open = false">

    {{-- Overlay --}}
    <div x-show="open"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6"
             @click.stop>
            <div class="flex items-center gap-3 mb-4">
                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                    <i class="fa-solid fa-triangle-exclamation text-red-600"></i>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-slate-800">Eliminar comercio</h3>
                    <p class="text-xs text-slate-500">Esta acción es irreversible.</p>
                </div>
            </div>

            <div class="bg-red-50 border border-red-200 rounded-lg px-3 py-2 text-xs text-red-700 mb-4 space-y-1">
                <p>Se eliminará <strong>todo</strong> lo asociado al comercio:</p>
                <p>productos, sucursales, listas de precios, importaciones y suscripción.</p>
                <p>Los usuarios quedarán sin comercio asignado.</p>
            </div>

            <p class="text-sm text-slate-700 mb-2">
                Escribí <strong x-text="storeName" class="font-mono"></strong> para confirmar:
            </p>
            <input type="text"
                   x-model="confirmName"
                   x-bind:placeholder="storeName"
                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-red-400 mb-4">

            <div class="flex justify-end gap-2">
                <button type="button"
                        @click="open = false"
                        class="text-sm text-slate-600 hover:text-slate-800 px-4 py-2 rounded-lg border border-slate-200 hover:bg-slate-50 transition">
                    Cancelar
                </button>
                <form x-bind:action="storeAction" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            x-bind:disabled="confirmName !== storeName"
                            class="text-sm font-medium px-4 py-2 rounded-lg transition
                                   bg-red-600 text-white hover:bg-red-700
                                   disabled:opacity-40 disabled:cursor-not-allowed">
                        Eliminar definitivamente
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ===== TABLA ===== --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <p class="text-sm text-slate-500">Total: {{ $stores->total() }} comercios</p>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-semibold text-slate-600">Comercio</th>
                        <th class="text-left px-4 py-3 font-semibold text-slate-600 hidden md:table-cell">Plan</th>
                        <th class="text-left px-4 py-3 font-semibold text-slate-600 hidden md:table-cell">Subscripción</th>
                        <th class="text-center px-4 py-3 font-semibold text-slate-600 hidden lg:table-cell">Productos</th>
                        <th class="text-center px-4 py-3 font-semibold text-slate-600 hidden lg:table-cell">Sucursales</th>
                        <th class="text-left px-4 py-3 font-semibold text-slate-600">Estado</th>
                        <th class="text-right px-4 py-3 font-semibold text-slate-600">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($stores as $store)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.stores.show', $store) }}"
                               class="font-medium text-blue-600 hover:underline">
                                {{ $store->name }}
                            </a>
                            @if($store->address)
                                <div class="text-xs text-slate-400 mt-0.5">{{ $store->address }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 hidden md:table-cell text-slate-600">
                            {{ $store->subscription?->plan?->name ?? '—' }}
                        </td>
                        <td class="px-4 py-3 hidden md:table-cell">
                            @php $sub = $store->subscription; @endphp
                            @if($sub)
                                @if($sub->status === 'trial')
                                    <span class="inline-flex items-center gap-1 text-xs font-medium bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full">
                                        <i class="fa-solid fa-hourglass-half text-[10px]"></i> Trial
                                    </span>
                                @elseif($sub->status === 'active')
                                    <span class="inline-flex items-center gap-1 text-xs font-medium bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">
                                        <i class="fa-solid fa-circle-check text-[10px]"></i> Activa
                                    </span>
                                @elseif($sub->status === 'suspended')
                                    <span class="inline-flex items-center gap-1 text-xs font-medium bg-red-100 text-red-700 px-2 py-0.5 rounded-full">
                                        <i class="fa-solid fa-ban text-[10px]"></i> Suspendida
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400">{{ $sub->status }}</span>
                                @endif
                            @else
                                <span class="text-xs text-slate-400">Sin sub</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center hidden lg:table-cell text-slate-600">
                            {{ number_format($store->products_count) }}
                        </td>
                        <td class="px-4 py-3 text-center hidden lg:table-cell text-slate-600">
                            {{ $store->branches_count }}
                        </td>
                        <td class="px-4 py-3">
                            @if($store->status === 'active')
                                <span class="inline-flex items-center gap-1 text-xs font-medium bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">
                                    <i class="fa-solid fa-circle text-[8px]"></i> Activo
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-xs font-medium bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full">
                                    <i class="fa-solid fa-circle text-[8px]"></i> Suspendido
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.stores.show', $store) }}"
                                   class="text-xs text-blue-600 hover:underline font-medium">Ver</a>

                                @if($store->status === 'active')
                                    <form method="POST" action="{{ route('admin.stores.suspend', $store) }}"
                                          onsubmit="return confirm('¿Suspender este comercio?')">
                                        @csrf
                                        <button type="submit"
                                                class="text-xs text-red-600 hover:underline font-medium">Suspender</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.stores.reactivate', $store) }}">
                                        @csrf
                                        <button type="submit"
                                                class="text-xs text-emerald-600 hover:underline font-medium">Reactivar</button>
                                    </form>
                                @endif

                                <button type="button"
                                        @click="launch('{{ addslashes($store->name) }}', '{{ route('admin.stores.destroy', $store) }}')"
                                        class="text-xs text-red-500 hover:text-red-700 font-medium hover:underline">
                                    Eliminar
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-slate-400">
                            No hay comercios registrados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($stores->hasPages())
    <div class="mt-4">{{ $stores->links() }}</div>
    @endif

</div>{{-- fin x-data --}}

@endsection
