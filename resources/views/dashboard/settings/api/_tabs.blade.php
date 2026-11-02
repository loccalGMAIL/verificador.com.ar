{{--
    Sub-pestañas de la sección API de Configuración.
    Variable esperada: $apiActiveTab (string) — 'basic' | 'odoo'
--}}
<div class="flex gap-1 mb-6 overflow-x-auto">
    @foreach([
        'basic' => ['icon' => 'fa-sliders', 'label' => 'Configuración básica', 'href' => route('dashboard.settings.api')],
        'odoo'  => ['icon' => 'fa-cube',    'label' => 'Odoo',                 'href' => route('dashboard.settings.odoo')],
    ] as $key => $tab)
    <a href="{{ $tab['href'] }}"
       class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium whitespace-nowrap rounded-lg transition
              {{ ($apiActiveTab ?? '') === $key
                    ? 'bg-blue-50 text-blue-700 ring-1 ring-blue-200'
                    : 'text-slate-500 hover:text-slate-700 hover:bg-slate-100' }}">
        <i class="fa-solid {{ $tab['icon'] }} text-[10px]"></i>
        {{ $tab['label'] }}
    </a>
    @endforeach
</div>
