<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ImportProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ImportProfileController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'           => ['required', 'string', 'max:100'],
            'description'    => ['nullable', 'string', 'max:255'],
            'header_mapping' => ['required', 'array'],
        ]);

        // Filtrar campos con valor vacío
        $data['header_mapping'] = array_filter(
            $data['header_mapping'],
            fn ($v) => $v !== null && $v !== ''
        );

        if (empty($data['header_mapping'])) {
            return back()->withErrors(['header_mapping' => 'El perfil debe tener al menos un campo mapeado.']);
        }

        $data['store_id'] = auth()->user()->store_id;

        ImportProfile::create($data);

        return redirect()->route('dashboard.settings', ['tab' => 'import-profiles'])
            ->with('success', "Perfil \"{$data['name']}\" creado correctamente.");
    }

    public function update(Request $request, ImportProfile $importProfile): RedirectResponse
    {
        $this->authorize($importProfile);

        $data = $request->validate([
            'name'           => ['required', 'string', 'max:100'],
            'description'    => ['nullable', 'string', 'max:255'],
            'header_mapping' => ['required', 'array'],
        ]);

        $data['header_mapping'] = array_filter(
            $data['header_mapping'],
            fn ($v) => $v !== null && $v !== ''
        );

        $importProfile->update($data);

        return redirect()->route('dashboard.settings', ['tab' => 'import-profiles'])
            ->with('success', 'Perfil actualizado correctamente.');
    }

    public function destroy(ImportProfile $importProfile): RedirectResponse
    {
        $this->authorize($importProfile);

        $name = $importProfile->name;
        $importProfile->delete();

        return redirect()->route('dashboard.settings', ['tab' => 'import-profiles'])
            ->with('success', "Perfil \"{$name}\" eliminado.");
    }

    private function authorize(ImportProfile $profile): void
    {
        abort_if($profile->store_id !== auth()->user()->store_id, 403);
    }
}
