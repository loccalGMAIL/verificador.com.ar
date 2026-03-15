@extends('layouts.admin')

@section('title', $section)
@section('page-title', $section)

@section('content')
<div class="flex flex-col items-center justify-center py-24 text-center">
    <div class="w-16 h-16 bg-slate-200 rounded-2xl flex items-center justify-center mb-5">
        <i class="fa-solid fa-hammer text-slate-500 text-2xl"></i>
    </div>
    <h2 class="text-xl font-bold text-slate-800 mb-2">En construcción</h2>
    <p class="text-slate-500 text-sm max-w-sm">
        La sección <strong>{{ $section }}</strong> está en desarrollo.
    </p>
    <a href="{{ route('admin.home') }}" class="mt-6 text-blue-600 text-sm font-medium hover:underline">
        ← Volver al dashboard
    </a>
</div>
@endsection
