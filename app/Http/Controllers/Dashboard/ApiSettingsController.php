<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ApiSettingsController extends Controller
{
    /** Configuración básica de API: estado del plan y resumen de integraciones */
    public function index(): View
    {
        $store = auth()->user()->store;
        $hasApi = (bool) $store->subscription?->hasFeature('has_api');
        $odooConnection = $store->odooConnection;
        $odooProductCount = $store->products()->where('source', 'odoo')->count();

        return view('dashboard.settings.api.index', compact('store', 'hasApi', 'odooConnection', 'odooProductCount'));
    }
}
