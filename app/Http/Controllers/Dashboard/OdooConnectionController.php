<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Jobs\SyncOdooProducts;
use App\Models\OdooConnection;
use App\Rules\PublicHttpsUrl;
use App\Services\Odoo\OdooClient;
use App\Services\Odoo\OdooException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OdooConnectionController extends Controller
{
    public function index(): View
    {
        $store = auth()->user()->store;
        $connection = $store->odooConnection;
        $odooProductCount = $store->products()->where('source', 'odoo')->count();

        return view('dashboard.settings.odoo', compact('store', 'connection', 'odooProductCount'));
    }

    /** Guarda (o actualiza) la conexión, probándola antes contra Odoo */
    public function update(Request $request): RedirectResponse
    {
        $store = auth()->user()->store;
        $connection = $store->odooConnection;

        $data = $request->validate([
            'url' => ['required', 'string', 'max:255', new PublicHttpsUrl],
            'database' => ['nullable', 'string', 'max:100', 'regex:/^[A-Za-z0-9_.-]+$/'],
            'api_key' => [$connection ? 'nullable' : 'required', 'string', 'max:255'],
        ], [
            'database.regex' => 'El nombre de la base solo puede tener letras, números, guiones, puntos y guiones bajos.',
            'api_key.required' => 'Pegá la API key generada en Odoo.',
        ]);

        $url = OdooConnection::normalizeUrl($data['url']);
        $database = trim((string) ($data['database'] ?? '')) ?: OdooConnection::guessDatabase($url);
        $apiKey = trim((string) ($data['api_key'] ?? '')) ?: $connection?->api_key;

        if (! $database) {
            return back()->withInput()
                ->withErrors(['database' => 'Tu Odoo usa un dominio propio: indicá el nombre de la base de datos.']);
        }

        try {
            $productCount = (new OdooClient($url, $database, $apiKey))->testConnection();
        } catch (OdooException $e) {
            return back()->withInput()->withErrors(['connection' => $e->getMessage()]);
        }

        $credentialsChanged = ! $connection
            || $connection->url !== $url
            || $connection->database !== $database;

        $connection = $store->odooConnection()->updateOrCreate([], [
            'url' => $url,
            'database' => $database,
            'api_key' => $apiKey,
            'enabled' => true,
            // Si cambió la instancia, la próxima sincronización trae todo el catálogo
            'sync_cursor' => $credentialsChanged ? null : $connection->sync_cursor,
            'last_sync_status' => null,
            'last_error' => null,
        ]);

        activity()->log('odoo.connected', $connection, ['url' => $url, 'database' => $database]);

        SyncOdooProducts::dispatchAfterResponse($connection);

        return redirect()->route('dashboard.settings.odoo')
            ->with('success', "Conexión exitosa: Odoo tiene {$productCount} productos con código de barras. La sincronización comenzó.");
    }

    public function sync(): RedirectResponse
    {
        $connection = auth()->user()->store->odooConnection;

        abort_unless($connection, 404);

        SyncOdooProducts::dispatchAfterResponse($connection);

        return redirect()->route('dashboard.settings.odoo')
            ->with('success', 'Sincronización iniciada. Actualizá la página en unos instantes para ver el resultado.');
    }

    /** Desconecta Odoo: los productos quedan y vuelven a ser editables */
    public function destroy(): RedirectResponse
    {
        $store = auth()->user()->store;
        $connection = $store->odooConnection;

        abort_unless($connection, 404);

        $store->products()->where('source', 'odoo')->update(['source' => null]);
        $connection->delete();

        activity()->log('odoo.disconnected', null, ['url' => $connection->url]);

        return redirect()->route('dashboard.settings.odoo')
            ->with('success', 'Odoo desconectado. Los productos se conservan y ahora podés editarlos manualmente.');
    }
}
