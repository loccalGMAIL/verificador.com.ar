@extends('layouts.app')

@section('title', 'Sucursales')
@section('page-title', 'Sucursales')

@section('content')

<div class="flex justify-end mb-5">
    <a href="{{ route('dashboard.branches.create') }}"
       class="flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-blue-700 transition">
        <i class="fa-solid fa-plus"></i>
        Nueva sucursal
    </a>
</div>

<div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200">
            <tr class="text-left">
                <th class="px-4 py-3 font-semibold text-slate-500">Sucursal</th>
                <th class="px-4 py-3 font-semibold text-slate-500 hidden sm:table-cell">Dirección</th>
                <th class="px-4 py-3 font-semibold text-slate-500">Estado</th>
                <th class="px-4 py-3 font-semibold text-slate-500 text-right">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($branches as $branch)
            <tr class="hover:bg-slate-50 transition">
                <td class="px-4 py-4">
                    <p class="font-medium text-slate-800">{{ $branch->name }}</p>
                    <p class="text-xs text-slate-400 font-mono mt-0.5 hidden sm:block">
                        Token: {{ substr($branch->qr_token, 0, 8) }}...
                    </p>
                </td>
                <td class="px-4 py-4 text-slate-600 hidden sm:table-cell">
                    {{ $branch->address ?: '—' }}
                </td>
                <td class="px-4 py-4">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                        {{ $branch->active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                        {{ $branch->active ? 'Activa' : 'Inactiva' }}
                    </span>
                </td>
                <td class="px-4 py-4 text-right">
                    <div class="flex items-center justify-end gap-1">
                        {{-- Configurar e imprimir QR --}}
                        <a href="{{ route('dashboard.branches.qr.configure', $branch) }}"
                           title="Configurar e imprimir QR"
                           class="text-emerald-600 hover:text-emerald-800 px-2 py-1 text-xs font-medium flex items-center gap-1">
                            <i class="fa-solid fa-print"></i>
                            <span class="hidden sm:inline">Imprimir QR</span>
                        </a>
                        <a href="{{ route('dashboard.branches.edit', $branch) }}"
                           class="text-blue-600 hover:text-blue-800 px-2 py-1 text-xs font-medium">
                            Editar
                        </a>
                        <form method="POST" action="{{ route('dashboard.branches.destroy', $branch) }}"
                              class="inline"
                              onsubmit="return confirm('¿Eliminar la sucursal {{ addslashes($branch->name) }}?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="text-red-500 hover:text-red-700 px-2 py-1 text-xs font-medium">
                                Eliminar
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="px-4 py-12 text-center">
                    <i class="fa-solid fa-store text-3xl text-slate-300 mb-3 block"></i>
                    <p class="text-slate-500 font-medium">No hay sucursales todavía.</p>
                    <p class="text-slate-400 text-xs mt-1">
                        Cada sucursal tiene su propio QR para que los clientes consulten precios.
                    </p>
                    <a href="{{ route('dashboard.branches.create') }}"
                       class="text-blue-600 text-sm mt-3 inline-block hover:underline">
                        Crear la primera sucursal
                    </a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
