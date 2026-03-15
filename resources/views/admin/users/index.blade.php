@extends('layouts.admin')

@section('title', 'Usuarios')
@section('page-title', 'Usuarios')

@section('content')

<div class="mb-5">
    <p class="text-sm text-slate-500">Total: {{ $users->total() }} usuarios</p>
</div>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold text-slate-600">Usuario</th>
                    <th class="text-left px-4 py-3 font-semibold text-slate-600 hidden md:table-cell">Comercio</th>
                    <th class="text-left px-4 py-3 font-semibold text-slate-600">Rol</th>
                    <th class="text-left px-4 py-3 font-semibold text-slate-600 hidden lg:table-cell">Plan</th>
                    <th class="text-left px-4 py-3 font-semibold text-slate-600 hidden lg:table-cell">Registrado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($users as $user)
                <tr class="hover:bg-slate-50 transition">
                    <td class="px-4 py-3">
                        <div class="font-medium text-slate-800">{{ $user->name }}</div>
                        <div class="text-xs text-slate-400">{{ $user->email }}</div>
                    </td>
                    <td class="px-4 py-3 hidden md:table-cell">
                        @if($user->store)
                            <a href="{{ route('admin.stores.show', $user->store) }}"
                               class="text-blue-600 hover:underline">
                                {{ $user->store->name }}
                            </a>
                        @else
                            <span class="text-slate-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center text-xs font-medium px-2 py-0.5 rounded-full
                            {{ $user->role === 'admin' ? 'bg-red-100 text-red-700' :
                               ($user->role === 'owner' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-600') }}">
                            {{ ucfirst($user->role) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 hidden lg:table-cell text-slate-600">
                        {{ $user->store?->subscription?->plan?->name ?? '—' }}
                    </td>
                    <td class="px-4 py-3 hidden lg:table-cell text-slate-500 text-xs">
                        {{ $user->created_at->format('d/m/Y') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-10 text-center text-slate-400">
                        No hay usuarios registrados.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($users->hasPages())
<div class="mt-4">{{ $users->links() }}</div>
@endif

@endsection
