@extends('layouts.app')

@section('title', 'Usuarios')
@section('page-title', 'Usuarios del comercio')

@section('content')

{{-- Banner: link recién generado --}}
@if(session('invite_link'))
<div class="mb-4 bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 flex items-start gap-3">
    <i class="fa-solid fa-circle-check text-emerald-500 mt-0.5"></i>
    <div class="flex-1 min-w-0">
        <p class="text-emerald-800 text-sm font-medium mb-1">Link de invitación generado</p>
        <div class="flex items-center gap-2">
            <input type="text"
                   id="invite-link-banner"
                   value="{{ session('invite_link') }}"
                   readonly
                   class="flex-1 min-w-0 text-xs font-mono bg-white border border-emerald-300 rounded-lg px-3 py-1.5 text-slate-700 truncate">
            <button onclick="navigator.clipboard.writeText(document.getElementById('invite-link-banner').value).then(() => { this.textContent = '¡Copiado!'; setTimeout(() => this.textContent = 'Copiar', 2000); })"
                    class="flex-shrink-0 text-xs bg-emerald-600 text-white font-medium px-3 py-1.5 rounded-lg hover:bg-emerald-700 transition">
                Copiar
            </button>
        </div>
    </div>
</div>
@endif

<div class="space-y-6">

    {{-- ====== TABLA DE USUARIOS ====== --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-slate-800">Miembros del comercio</h2>
            <span class="text-xs text-slate-500">{{ $users->count() }} {{ $users->count() === 1 ? 'usuario' : 'usuarios' }}</span>
        </div>

        @if($users->isEmpty())
            <div class="px-5 py-10 text-center text-slate-400 text-sm">
                <i class="fa-solid fa-users text-2xl mb-2 block text-slate-300"></i>
                Todavía no hay empleados en tu comercio.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Nombre</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Email</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Rol</th>
                            @if(auth()->user()->isOwner())
                            <th class="px-5 py-3"></th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($users as $member)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-5 py-3 font-medium text-slate-800">{{ $member->name }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $member->email }}</td>
                            <td class="px-5 py-3">
                                @if($member->isOwner())
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold bg-blue-100 text-blue-700 px-2.5 py-0.5 rounded-full">
                                        <i class="fa-solid fa-crown text-[10px]"></i> Dueño
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold bg-slate-100 text-slate-600 px-2.5 py-0.5 rounded-full">
                                        <i class="fa-solid fa-user text-[10px]"></i> Empleado
                                    </span>
                                @endif
                            </td>
                            @if(auth()->user()->isOwner())
                            <td class="px-5 py-3 text-right">
                                @if($member->isEmployee())
                                <form method="POST" action="{{ route('dashboard.users.remove', $member) }}"
                                      onsubmit="return confirm('¿Estás seguro de quitar a {{ addslashes($member->name) }} del comercio?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-xs text-red-600 hover:text-red-800 font-medium transition">
                                        <i class="fa-solid fa-user-minus mr-1"></i>Quitar
                                    </button>
                                </form>
                                @endif
                            </td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- ====== PANEL DE INVITACIÓN (solo owner) ====== --}}
    @if(auth()->user()->isOwner())
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h2 class="text-sm font-semibold text-slate-800">Invitar empleados</h2>
            <p class="text-xs text-slate-500 mt-0.5">Compartí este link para que otros se unan a tu comercio como empleados.</p>
        </div>
        <div class="px-5 py-5 space-y-4">

            @if($store->invite_token)
            {{-- Link existente --}}
            <div x-data="{ copied: false }">
                <label class="block text-xs font-medium text-slate-600 mb-1.5">Link de invitación activo</label>
                <div class="flex items-center gap-2">
                    <input type="text"
                           x-ref="inviteLink"
                           value="{{ route('invite.show', $store->invite_token) }}"
                           readonly
                           class="flex-1 min-w-0 text-xs font-mono bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-slate-700">
                    <button @click="navigator.clipboard.writeText($refs.inviteLink.value).then(() => { copied = true; setTimeout(() => copied = false, 2000) })"
                            class="flex-shrink-0 text-xs bg-blue-600 text-white font-medium px-3 py-2 rounded-lg hover:bg-blue-700 transition">
                        <span x-show="!copied">Copiar</span>
                        <span x-show="copied">¡Copiado!</span>
                    </button>
                </div>

                {{-- Compartir por WhatsApp --}}
                @php
                    $inviteUrl = route('invite.show', $store->invite_token);
                    $waText = "¡Hola! Te invito a unirte a {$store->name} en verificador.com.ar. Usá este link para ingresar: {$inviteUrl}";
                @endphp
                <a href="https://wa.me/?text={{ rawurlencode($waText) }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="mt-2 inline-flex items-center gap-2 text-xs font-medium text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 px-3 py-2 rounded-lg transition">
                    <i class="fa-brands fa-whatsapp text-base"></i>
                    Compartir por WhatsApp
                </a>

            </div>
            @endif

            {{-- Generar / Regenerar link --}}
            <form method="POST" action="{{ route('dashboard.users.generate-invite') }}"
                  @if($store->invite_token)
                  onsubmit="return confirm('¿Generar un nuevo link? El link anterior quedará inválido.')"
                  @endif>
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-2 text-sm font-medium bg-slate-800 text-white px-4 py-2 rounded-lg hover:bg-slate-700 transition">
                    <i class="fa-solid fa-rotate-right"></i>
                    {{ $store->invite_token ? 'Regenerar link' : 'Generar link de invitación' }}
                </button>
                @if($store->invite_token)
                <p class="mt-1.5 text-xs text-slate-400">Regenerar invalida el link anterior.</p>
                @endif
            </form>

        </div>
    </div>
    @endif

</div>

@endsection
