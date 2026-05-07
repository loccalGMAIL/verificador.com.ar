<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ProductCustomFieldDefinition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomFieldController extends Controller
{
    public function index(): View
    {
        $store = auth()->user()->store;
        $fields = $store->customFieldDefinitions()->get();

        return view('dashboard.settings.custom-fields', compact('store', 'fields'));
    }

    public function store(Request $request): RedirectResponse
    {
        $store = auth()->user()->store;

        $data = $request->validate([
            'label' => ['required', 'string', 'max:100'],
            'excel_column' => ['required', 'string', 'max:100', 'alpha_dash'],
            'visible_on_scan' => ['boolean'],
        ]);

        $data['store_id'] = $store->id;
        $data['sort_order'] = $store->customFieldDefinitions()->max('sort_order') + 1;
        $data['visible_on_scan'] = $request->boolean('visible_on_scan', true);

        $exists = $store->customFieldDefinitions()
            ->where('excel_column', $data['excel_column'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['excel_column' => 'Ya existe un campo con esa columna Excel.'])->withInput();
        }

        ProductCustomFieldDefinition::create($data);

        return redirect()->route('dashboard.settings.custom-fields.index')
            ->with('success', "Campo \"{$data['label']}\" creado correctamente.");
    }

    public function update(Request $request, ProductCustomFieldDefinition $customField): RedirectResponse
    {
        $this->authorize($customField);

        $data = $request->validate([
            'label' => ['required', 'string', 'max:100'],
            'excel_column' => ['required', 'string', 'max:100', 'alpha_dash'],
            'visible_on_scan' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $store = auth()->user()->store;

        $exists = $store->customFieldDefinitions()
            ->where('excel_column', $data['excel_column'])
            ->where('id', '!=', $customField->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['excel_column' => 'Ya existe otro campo con esa columna Excel.'])->withInput();
        }

        $data['visible_on_scan'] = $request->boolean('visible_on_scan', false);

        $customField->update($data);

        return redirect()->route('dashboard.settings.custom-fields.index')
            ->with('success', 'Campo actualizado correctamente.');
    }

    public function destroy(ProductCustomFieldDefinition $customField): RedirectResponse
    {
        $this->authorize($customField);

        $label = $customField->label;
        $customField->delete();

        return redirect()->route('dashboard.settings.custom-fields.index')
            ->with('success', "Campo \"{$label}\" eliminado.");
    }

    private function authorize(ProductCustomFieldDefinition $field): void
    {
        abort_if($field->store_id !== auth()->user()->store_id, 403);
    }
}
