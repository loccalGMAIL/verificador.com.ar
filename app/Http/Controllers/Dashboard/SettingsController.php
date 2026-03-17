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
        $store          = auth()->user()->store;
        $importProfiles = $store->importProfiles()->latest()->get();

        return view('dashboard.settings', compact('store', 'importProfiles'));
    }

    public function update(Request $request): RedirectResponse
    {
        $store = auth()->user()->store;
        $tab   = $request->input('_tab', 'general');

        if ($tab === 'excel-import') {
            $data = $request->validate([
                'excel_col_barcode'  => ['required', 'string', 'max:100'],
                'excel_col_name'     => ['required', 'string', 'max:100'],
                'excel_col_price'    => ['required', 'string', 'max:100'],
                'retail_label'       => ['required', 'string', 'max:100'],
                'show_wholesale'     => ['boolean'],
                'wholesale_label'    => ['nullable', 'string', 'max:100'],
                'wholesale_discount' => ['nullable', 'numeric', 'min:0', 'max:100'],
            ]);

            $data['show_wholesale'] = $request->boolean('show_wholesale');

            $store->update($data);

            return redirect()->route('dashboard.settings', ['tab' => 'excel-import'])
                ->with('success', 'Configuración de importación guardada.');
        }

        // Tab: general (default)
        $data = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone'   => ['nullable', 'string', 'max:50'],
            'logo'    => ['nullable', 'image', 'max:2048'],
        ]);

        if ($data['name'] !== $store->name) {
            $data['slug'] = Str::slug($data['name']) . '-' . Str::random(6);
        }

        if ($request->hasFile('logo')) {
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
