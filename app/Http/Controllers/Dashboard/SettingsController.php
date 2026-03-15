<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function show(): View
    {
        $store = auth()->user()->store;
        return view('dashboard.settings', compact('store'));
    }

    public function update(Request $request): RedirectResponse
    {
        $store = auth()->user()->store;

        $data = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone'   => ['nullable', 'string', 'max:50'],
            'logo'    => ['nullable', 'image', 'max:2048'],
        ]);

        // Actualizar slug si el nombre cambió
        if ($data['name'] !== $store->name) {
            $data['slug'] = Str::slug($data['name']) . '-' . Str::random(6);
        }

        if ($request->hasFile('logo')) {
            // Eliminar logo anterior
            if ($store->logo_path) {
                Storage::disk('public')->delete($store->logo_path);
            }
            $data['logo_path'] = $request->file('logo')
                ->store("logos/{$store->id}", 'public');
        }

        $store->update($data);

        return redirect()->route('dashboard.settings')
            ->with('success', 'Configuración guardada.');
    }
}
