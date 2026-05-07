{{--
    Barra de pestañas compartida entre todas las vistas de Configuración.
    Variable esperada: $settingsActiveTab (string) — identifica la pestaña activa.
    Valores: 'general' | 'excel-import' | 'print' | 'appearance' | 'custom-fields' | 'users'
--}}
<div class="flex gap-1 mb-6 border-b border-slate-200 overflow-x-auto">
    @foreach([
        'general'      => ['icon' => 'fa-store',         'label' => 'General',            'href' => route('dashboard.settings', ['tab' => 'general'])],
        'excel-import' => ['icon' => 'fa-file-excel',    'label' => 'Importación Excel',   'href' => route('dashboard.settings', ['tab' => 'excel-import'])],
        'custom-fields'=> ['icon' => 'fa-table-columns', 'label' => 'Campos extra',        'href' => route('dashboard.settings.custom-fields.index')],
        'print'        => ['icon' => 'fa-print',         'label' => 'Impresión QR',        'href' => route('dashboard.settings', ['tab' => 'print'])],
        'appearance'   => ['icon' => 'fa-palette',       'label' => 'Apariencia',          'href' => route('dashboard.settings', ['tab' => 'appearance'])],
        'users'        => ['icon' => 'fa-users',         'label' => 'Usuarios',            'href' => route('dashboard.users.index')],
    ] as $key => $tab)
    <a href="{{ $tab['href'] }}"
       class="flex items-center gap-1.5 px-4 py-2.5 text-sm font-medium whitespace-nowrap border-b-2 -mb-px transition
              {{ ($settingsActiveTab ?? '') === $key
                    ? 'border-blue-600 text-blue-600'
                    : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
        <i class="fa-solid {{ $tab['icon'] }} text-xs"></i>
        {{ $tab['label'] }}
    </a>
    @endforeach
</div>
