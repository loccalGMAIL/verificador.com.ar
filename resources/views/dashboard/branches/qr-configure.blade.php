@extends('layouts.app')

@section('title', 'Configurar impresión QR – ' . $branch->name)
@section('page-title', 'Configurar impresión QR')

@section('content')

{{-- Cabecera --}}
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('dashboard.branches.index') }}"
       class="text-slate-400 hover:text-slate-700 transition">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <div>
        <h2 class="text-base font-semibold text-slate-800">{{ $branch->name }}</h2>
        <p class="text-xs text-slate-400">Personalizá el cartel antes de imprimir</p>
    </div>
</div>

@include('dashboard.branches._qr-configure-form', ['branch' => $branch])

@endsection
