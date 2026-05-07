@extends('layouts.admin')

@section('title', 'Actividad')
@section('page-title', 'Registro de Actividad')

@section('content')
<div class="space-y-6">
    <!-- Filtros -->
    <form method="GET" action="{{ route('admin.activity.index') }}" class="bg-white rounded-lg shadow p-4 space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label for="store_id" class="block text-sm font-medium text-gray-700">Comercio</label>
                <select name="store_id" id="store_id"
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">— Todos —</option>
                    @foreach ($stores as $id => $name)
                        <option value="{{ $id }}" @selected(request('store_id') == $id)>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="event_type" class="block text-sm font-medium text-gray-700">Tipo de evento</label>
                <select name="event_type" id="event_type"
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">— Todos —</option>
                    @foreach ($eventTypes as $type)
                        <option value="{{ $type }}" @selected(request('event_type') == $type)>
                            {{ $type }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="from" class="block text-sm font-medium text-gray-700">Desde</label>
                <input type="date" name="from" id="from" value="{{ request('from') }}"
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div>
                <label for="to" class="block text-sm font-medium text-gray-700">Hasta</label>
                <input type="date" name="to" id="to" value="{{ request('to') }}"
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                Filtrar
            </button>
            <a href="{{ route('admin.activity.index') }}" class="inline-flex items-center rounded-md bg-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-300">
                Limpiar
            </a>
        </div>
    </form>

    <!-- Tabla de eventos -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comercio</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Evento</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($logs as $log)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $log->created_at?->setTimezone('America/Argentina/Buenos_Aires')->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @if ($log->store)
                                <a href="{{ route('admin.stores.show', $log->store) }}" class="text-indigo-600 hover:text-indigo-900">
                                    {{ $log->store->name }}
                                </a>
                            @else
                                <span class="text-gray-500">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @if ($log->causer)
                                {{ $log->causer->name ?? $log->causer->email }}
                            @else
                                <span class="text-gray-500">Sistema</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span class="inline-flex items-center rounded-full bg-indigo-100 px-3 py-1 text-sm font-medium text-indigo-800">
                                {{ $log->event_type }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $log->ip_address ?? '—' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                            Sin eventos registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div>
        {{ $logs->links() }}
    </div>
</div>
@endsection
