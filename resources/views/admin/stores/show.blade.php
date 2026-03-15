@extends('layouts.admin')

@section('title', $store->name)
@section('page-title', $store->name)

@section('content')

{{-- Breadcrumb --}}
<div class="flex items-center gap-2 text-sm text-slate-500 mb-5">
    <a href="{{ route('admin.stores.index') }}" class="hover:text-blue-600 transition">Comercios</a>
    <i class="fa-solid fa-chevron-right text-xs"></i>
    <span class="text-slate-800 font-medium">{{ $store->name }}</span>
</div>

{{-- Header + actions --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
        @if($store->logo_path)
            <img src="{{ Storage::url($store->logo_path) }}" alt="Logo"
                 class="w-12 h-12 rounded-xl object-cover border border-slate-200 shadow-sm">
        @else
            <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center shadow-sm">
                <i class="fa-solid fa-shop text-xl"></i>
            </div>
        @endif
        <div>
            <h2 class="text-lg font-bold text-slate-800">{{ $store->name }}</h2>
            @if($store->address)
                <p class="text-sm text-slate-500">{{ $store->address }}</p>
            @endif
        </div>
    </div>
    <div class="flex gap-2">
        @if($store->status === 'active')
            <form method="POST" action="{{ route('admin.stores.suspend', $store) }}"
                  onsubmit="return confirm('¿Suspender este comercio?')">
                @csrf
                <button type="submit"
                        class="flex items-center gap-2 px-4 py-2 text-sm font-medium bg-red-50 text-red-700 border border-red-200 rounded-lg hover:bg-red-100 transition">
                    <i class="fa-solid fa-ban"></i> Suspender
                </button>
            </form>
        @else
            <form method="POST" action="{{ route('admin.stores.reactivate', $store) }}">
                @csrf
                <button type="submit"
                        class="flex items-center gap-2 px-4 py-2 text-sm font-medium bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg hover:bg-emerald-100 transition">
                    <i class="fa-solid fa-circle-check"></i> Reactivar
                </button>
            </form>
        @endif
    </div>
</div>

{{-- Stats --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm text-center">
        <div class="text-2xl font-extrabold text-blue-600">{{ number_format($store->products_count) }}</div>
        <div class="text-xs text-slate-500 mt-1">Productos</div>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm text-center">
        <div class="text-2xl font-extrabold text-blue-600">{{ $store->branches_count }}</div>
        <div class="text-xs text-slate-500 mt-1">Sucursales</div>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm text-center">
        <div class="text-2xl font-extrabold text-blue-600">{{ $store->users_count }}</div>
        <div class="text-xs text-slate-500 mt-1">Usuarios</div>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm text-center">
        @php $sub = $store->subscription; @endphp
        @if($sub)
            <div class="text-lg font-extrabold
                {{ $sub->status === 'active' ? 'text-emerald-600' : ($sub->status === 'trial' ? 'text-amber-600' : 'text-red-600') }}">
                {{ ucfirst($sub->status) }}
            </div>
            <div class="text-xs text-slate-500 mt-1">{{ $sub->plan?->name ?? '—' }}</div>
        @else
            <div class="text-lg font-extrabold text-slate-400">Sin sub</div>
            <div class="text-xs text-slate-500 mt-1">—</div>
        @endif
    </div>
</div>

<div class="grid lg:grid-cols-2 gap-5">

    {{-- Subscripción activa --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
        <h3 class="font-semibold text-slate-700 mb-3 flex items-center gap-2">
            <i class="fa-solid fa-credit-card text-blue-500"></i> Subscripción
        </h3>
        @if($sub)
            <div class="space-y-2 text-sm text-slate-600">
                <div class="flex justify-between">
                    <span class="text-slate-400">Plan</span>
                    <span class="font-medium">{{ $sub->plan?->name ?? '—' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Estado</span>
                    <span class="font-medium">{{ ucfirst($sub->status) }}</span>
                </div>
                @if($sub->trial_ends_at)
                <div class="flex justify-between">
                    <span class="text-slate-400">Trial hasta</span>
                    <span>{{ $sub->trial_ends_at->format('d/m/Y') }}</span>
                </div>
                @endif
                @if($sub->ends_at)
                <div class="flex justify-between">
                    <span class="text-slate-400">Vence</span>
                    <span>{{ $sub->ends_at->format('d/m/Y') }}</span>
                </div>
                @endif
            </div>

            {{-- Change plan --}}
            <form method="POST" action="{{ route('admin.subscriptions.change-plan', $sub) }}" class="mt-4 flex gap-2">
                @csrf
                <select name="plan_id"
                        class="flex-1 border border-slate-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    @foreach(\App\Models\Plan::where('active', true)->orderBy('sort_order')->get() as $plan)
                        <option value="{{ $plan->id }}" {{ $sub->plan_id == $plan->id ? 'selected' : '' }}>
                            {{ $plan->name }} — ${{ $plan->price_usd }}
                        </option>
                    @endforeach
                </select>
                <button type="submit"
                        class="px-3 py-1.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
                    Cambiar
                </button>
            </form>

            {{-- Acciones de subscripción --}}
            <div class="mt-2 flex flex-wrap gap-3">
                {{-- Reset trial --}}
                <form method="POST" action="{{ route('admin.subscriptions.reset-trial', $sub) }}"
                      onsubmit="return confirm('¿Reiniciar el período trial por 7 días?')">
                    @csrf
                    <button type="submit"
                            class="text-xs text-amber-600 hover:underline font-medium">
                        <i class="fa-solid fa-rotate-left mr-1"></i>Reiniciar trial
                    </button>
                </form>

                {{-- Suspend / reactivate --}}
                @if($sub->status !== 'suspended')
                    <form method="POST" action="{{ route('admin.subscriptions.suspend', $sub) }}"
                          onsubmit="return confirm('¿Suspender la subscripción?')">
                        @csrf
                        <button type="submit"
                                class="text-xs text-red-600 hover:underline font-medium">Suspender sub.</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('admin.subscriptions.reactivate', $sub) }}">
                        @csrf
                        <button type="submit"
                                class="text-xs text-emerald-600 hover:underline font-medium">Reactivar sub.</button>
                    </form>
                @endif
            </div>
        @else
            <p class="text-sm text-slate-400">Este comercio no tiene subscripción.</p>
        @endif
    </div>

    {{-- Usuarios --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
        <h3 class="font-semibold text-slate-700 mb-3 flex items-center gap-2">
            <i class="fa-solid fa-users text-blue-500"></i> Usuarios
        </h3>
        @if($store->users->isEmpty())
            <p class="text-sm text-slate-400">Sin usuarios.</p>
        @else
            <div class="space-y-2">
                @foreach($store->users as $user)
                <div class="flex items-center justify-between text-sm">
                    <div>
                        <span class="font-medium text-slate-700">{{ $user->name }}</span>
                        <span class="text-slate-400 ml-2 text-xs">{{ $user->email }}</span>
                    </div>
                    <span class="text-xs px-2 py-0.5 rounded-full
                        {{ $user->role === 'owner' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-500' }}">
                        {{ ucfirst($user->role) }}
                    </span>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Sucursales --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
        <h3 class="font-semibold text-slate-700 mb-3 flex items-center gap-2">
            <i class="fa-solid fa-map-marker-alt text-blue-500"></i> Sucursales
        </h3>
        @if($store->branches->isEmpty())
            <p class="text-sm text-slate-400">Sin sucursales.</p>
        @else
            <div class="space-y-2">
                @foreach($store->branches as $branch)
                <div class="flex items-center justify-between text-sm">
                    <div>
                        <span class="font-medium text-slate-700">{{ $branch->name }}</span>
                        @if($branch->address)
                            <span class="text-slate-400 ml-2 text-xs">{{ $branch->address }}</span>
                        @endif
                    </div>
                    <span class="text-xs px-2 py-0.5 rounded-full
                        {{ $branch->active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                        {{ $branch->active ? 'Activa' : 'Inactiva' }}
                    </span>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Importaciones recientes --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
        <h3 class="font-semibold text-slate-700 mb-3 flex items-center gap-2">
            <i class="fa-solid fa-file-import text-blue-500"></i> Importaciones recientes
        </h3>
        @if($store->productImports->isEmpty())
            <p class="text-sm text-slate-400">Sin importaciones.</p>
        @else
            <div class="space-y-2">
                @foreach($store->productImports as $import)
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-600 text-xs">{{ $import->created_at->format('d/m/Y H:i') }}</span>
                    <span class="text-xs px-2 py-0.5 rounded-full
                        {{ $import->status === 'done' ? 'bg-emerald-100 text-emerald-700' :
                           ($import->status === 'failed' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
                        {{ ucfirst($import->status) }}
                    </span>
                    <span class="text-slate-500 text-xs">{{ $import->rows_ok ?? '—' }} filas OK</span>
                </div>
                @endforeach
            </div>
        @endif
    </div>

</div>

@endsection
