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
                    <th class="text-left px-4 py-3 font-semibold text-slate-600 hidden lg:table-cell">Estado</th>
                    <th class="text-left px-4 py-3 font-semibold text-slate-600 hidden lg:table-cell">Plan</th>
                    <th class="text-left px-4 py-3 font-semibold text-slate-600 hidden lg:table-cell">Registrado</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($users as $user)
                <tr class="hover:bg-slate-50 transition"
                    x-data="{ editOpen: false, reassignOpen: false, resetPasswordOpen: false }">

                    <td class="px-4 py-3">
                        <div class="font-medium text-slate-800">{{ $user->name }}</div>
                        <div class="text-xs text-slate-400">{{ $user->email }}</div>
                        @if($user->google_id)
                        <div class="text-xs text-blue-500 mt-0.5"><i class="fa-brands fa-google text-[10px]"></i> Google</div>
                        @endif
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
                            {{ $user->role === 'admin' ? 'Admin' : ($user->role === 'owner' ? 'Dueño' : 'Empleado') }}
                        </span>
                    </td>
                    <td class="px-4 py-3 hidden lg:table-cell">
                        @if($user->status === 'suspended')
                        <span class="inline-flex items-center text-xs font-medium px-2 py-0.5 rounded-full bg-red-100 text-red-700">
                            <i class="fa-solid fa-ban mr-1 text-[10px]"></i> Suspendido
                        </span>
                        @else
                        <span class="inline-flex items-center text-xs font-medium px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700">
                            <i class="fa-solid fa-circle-check mr-1 text-[10px]"></i> Activo
                        </span>
                        @endif
                    </td>
                    <td class="px-4 py-3 hidden lg:table-cell text-slate-600">
                        {{ $user->store?->subscription?->plan?->name ?? '—' }}
                    </td>
                    <td class="px-4 py-3 hidden lg:table-cell text-slate-500 text-xs">
                        {{ $user->created_at->format('d/m/Y') }}
                    </td>

                    {{-- Acciones --}}
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-1.5 justify-end">

                            {{-- Editar --}}
                            <button @click="editOpen = true"
                                    class="text-xs text-slate-600 hover:text-slate-900 font-medium bg-slate-100 hover:bg-slate-200 px-2.5 py-1 rounded-lg transition"
                                    title="Editar nombre / email / rol">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>

                            {{-- Resetear contraseña --}}
                            <button @click="resetPasswordOpen = true"
                                    class="text-xs text-indigo-700 hover:text-indigo-900 font-medium bg-indigo-50 hover:bg-indigo-100 px-2.5 py-1 rounded-lg transition"
                                    title="Resetear contraseña">
                                <i class="fa-solid fa-key"></i>
                            </button>

                            {{-- Reasignar comercio --}}
                            <button @click="reassignOpen = true"
                                    class="text-xs text-amber-700 hover:text-amber-900 font-medium bg-amber-50 hover:bg-amber-100 px-2.5 py-1 rounded-lg transition"
                                    title="Reasignar comercio">
                                <i class="fa-solid fa-right-left"></i>
                            </button>

                            {{-- Suspender / Reactivar --}}
                            @if($user->status === 'suspended')
                            <form method="POST" action="{{ route('admin.users.reactivate', $user) }}">
                                @csrf
                                <button type="submit"
                                        class="text-xs text-emerald-700 hover:text-emerald-900 font-medium bg-emerald-50 hover:bg-emerald-100 px-2.5 py-1 rounded-lg transition"
                                        title="Reactivar usuario">
                                    <i class="fa-solid fa-rotate-left"></i>
                                </button>
                            </form>
                            @else
                            <form method="POST" action="{{ route('admin.users.suspend', $user) }}"
                                  @if($user->id === auth()->id())
                                  onsubmit="return false"
                                  @else
                                  onsubmit="return confirm('¿Suspender a {{ addslashes($user->name) }}? No podrá iniciar sesión.')"
                                  @endif>
                                @csrf
                                <button type="submit"
                                        class="text-xs font-medium px-2.5 py-1 rounded-lg transition
                                               {{ $user->id === auth()->id()
                                                  ? 'text-slate-400 bg-slate-50 cursor-not-allowed'
                                                  : 'text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100' }}"
                                        title="{{ $user->id === auth()->id() ? 'No podés suspenderte a vos mismo' : 'Suspender usuario' }}">
                                    <i class="fa-solid fa-ban"></i>
                                </button>
                            </form>
                            @endif

                            {{-- Impersonar --}}
                            @if(! $user->isAdmin())
                            <form method="POST" action="{{ route('admin.users.impersonate', $user) }}">
                                @csrf
                                <button type="submit"
                                        class="text-xs text-violet-700 hover:text-violet-900 font-medium bg-violet-50 hover:bg-violet-100 px-2.5 py-1 rounded-lg transition"
                                        title="Impersonar usuario">
                                    <i class="fa-solid fa-user-secret"></i>
                                </button>
                            </form>
                            @endif
                        </div>

                        {{-- ===== MODAL: Editar ===== --}}
                        <div x-show="editOpen"
                             x-cloak
                             class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
                             @keydown.escape.window="editOpen = false">
                            <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6" @click.stop>
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-base font-semibold text-slate-800">Editar usuario</h3>
                                    <button @click="editOpen = false" class="text-slate-400 hover:text-slate-600">
                                        <i class="fa-solid fa-xmark text-lg"></i>
                                    </button>
                                </div>

                                <form method="POST" action="{{ route('admin.users.update', $user) }}">
                                    @csrf
                                    @method('PUT')
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-xs font-medium text-slate-600 mb-1">Nombre</label>
                                            <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                                                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-slate-600 mb-1">Email</label>
                                            <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                                                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-slate-600 mb-1">Rol</label>
                                            <select name="role" required
                                                    class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                <option value="owner"    {{ $user->role === 'owner'    ? 'selected' : '' }}>Dueño</option>
                                                <option value="employee" {{ $user->role === 'employee' ? 'selected' : '' }}>Empleado</option>
                                                <option value="admin"    {{ $user->role === 'admin'    ? 'selected' : '' }}>Admin</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="flex justify-end gap-2 mt-5">
                                        <button type="button" @click="editOpen = false"
                                                class="text-sm text-slate-600 hover:text-slate-800 px-4 py-2 rounded-lg border border-slate-200 hover:bg-slate-50 transition">
                                            Cancelar
                                        </button>
                                        <button type="submit"
                                                class="text-sm bg-blue-600 text-white font-medium px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                                            Guardar
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        {{-- ===== MODAL: Reasignar comercio ===== --}}
                        <div x-show="reassignOpen"
                             x-cloak
                             class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
                             @keydown.escape.window="reassignOpen = false">
                            <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6" @click.stop>
                                <div class="flex items-center justify-between mb-1">
                                    <h3 class="text-base font-semibold text-slate-800">Reasignar comercio</h3>
                                    <button @click="reassignOpen = false" class="text-slate-400 hover:text-slate-600">
                                        <i class="fa-solid fa-xmark text-lg"></i>
                                    </button>
                                </div>
                                <p class="text-xs text-slate-500 mb-4">
                                    Usuario: <strong class="text-slate-700">{{ $user->name }}</strong>
                                    &mdash; comercio actual:
                                    <strong class="text-slate-700">{{ $user->store?->name ?? 'ninguno' }}</strong>
                                </p>

                                <form method="POST" action="{{ route('admin.users.reassign', $user) }}">
                                    @csrf
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-xs font-medium text-slate-600 mb-1">Nuevo comercio</label>
                                            <select name="store_id"
                                                    class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-400">
                                                <option value="">— Sin comercio —</option>
                                                @foreach($stores as $store)
                                                <option value="{{ $store->id }}"
                                                        {{ $user->store_id == $store->id ? 'selected' : '' }}>
                                                    {{ $store->name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-slate-600 mb-1">Rol en ese comercio</label>
                                            <select name="role"
                                                    class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-400">
                                                <option value="owner"    {{ $user->role === 'owner'    ? 'selected' : '' }}>Dueño</option>
                                                <option value="employee" {{ $user->role === 'employee' ? 'selected' : '' }}>Empleado</option>
                                            </select>
                                            <p class="text-xs text-slate-400 mt-1">Ignorado si no se asigna comercio.</p>
                                        </div>
                                    </div>
                                    <div class="flex justify-end gap-2 mt-5">
                                        <button type="button" @click="reassignOpen = false"
                                                class="text-sm text-slate-600 hover:text-slate-800 px-4 py-2 rounded-lg border border-slate-200 hover:bg-slate-50 transition">
                                            Cancelar
                                        </button>
                                        <button type="submit"
                                                class="text-sm bg-amber-500 text-white font-medium px-4 py-2 rounded-lg hover:bg-amber-600 transition">
                                            Reasignar
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        {{-- ===== MODAL: Resetear contraseña ===== --}}
                        <div x-show="resetPasswordOpen"
                             x-cloak
                             class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
                             @keydown.escape.window="resetPasswordOpen = false">
                            <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6" @click.stop>
                                <div class="flex items-center justify-between mb-1">
                                    <h3 class="text-base font-semibold text-slate-800">Resetear contraseña</h3>
                                    <button @click="resetPasswordOpen = false" class="text-slate-400 hover:text-slate-600">
                                        <i class="fa-solid fa-xmark text-lg"></i>
                                    </button>
                                </div>
                                <p class="text-xs text-slate-500 mb-4">
                                    Usuario: <strong class="text-slate-700">{{ $user->name }}</strong>
                                    @if($user->google_id)
                                        &mdash; <span class="text-blue-500"><i class="fa-brands fa-google text-[10px]"></i> Google OAuth</span>
                                    @endif
                                </p>
                                @if($user->google_id && $user->password === null)
                                <div class="bg-amber-50 border border-amber-200 rounded-lg px-3 py-2 text-xs text-amber-700 mb-4">
                                    No tiene contraseña local. Al establecer una, podrá iniciar sesión también con email y contraseña.
                                </div>
                                @endif
                                <form method="POST" action="{{ route('admin.users.reset-password', $user) }}">
                                    @csrf
                                    @method('PUT')
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-xs font-medium text-slate-600 mb-1">Nueva contraseña</label>
                                            <input type="password" name="new_password" required minlength="8" autocomplete="new-password"
                                                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-slate-600 mb-1">Confirmar nueva contraseña</label>
                                            <input type="password" name="new_password_confirmation" required minlength="8" autocomplete="new-password"
                                                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                        </div>
                                    </div>
                                    <div class="flex justify-end gap-2 mt-5">
                                        <button type="button" @click="resetPasswordOpen = false"
                                                class="text-sm text-slate-600 hover:text-slate-800 px-4 py-2 rounded-lg border border-slate-200 hover:bg-slate-50 transition">
                                            Cancelar
                                        </button>
                                        <button type="submit"
                                                class="text-sm bg-indigo-600 text-white font-medium px-4 py-2 rounded-lg hover:bg-indigo-700 transition">
                                            Establecer contraseña
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-10 text-center text-slate-400">
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
