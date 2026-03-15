<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ScanController extends Controller
{
    /**
     * Consulta el precio de un producto dado el token de la sucursal y el código de barras.
     * GET /api/scan/{token}/{barcode}
     */
    public function __invoke(string $token, string $barcode): JsonResponse
    {
        $branch = Branch::where('qr_token', $token)
            ->where('active', true)
            ->with('store')
            ->first();

        if (! $branch) {
            return response()->json(['found' => false, 'error' => 'Sucursal no encontrada.'], 404);
        }

        if (! $branch->store->hasActiveSubscription()) {
            return response()->json(['found' => false, 'error' => 'Servicio no disponible.'], 403);
        }

        $product = Product::where('store_id', $branch->store_id)
            ->where('barcode', $barcode)
            ->where('active', true)
            ->first();

        if (! $product) {
            return response()->json(['found' => false]);
        }

        // Determinar precio y etiqueta según la moneda por defecto
        if ($product->currency_default === 'USD') {
            $price         = '$ ' . number_format((float) $product->price_usd, 2, ',', '.');
            $currencyLabel = 'Dólares (USD)';
        } else {
            $price         = '$ ' . number_format((float) $product->price_ars, 2, ',', '.');
            $currencyLabel = 'Pesos argentinos (ARS)';
        }

        return response()->json([
            'found'          => true,
            'name'           => $product->name,
            'price'          => $price,
            'currency_label' => $currencyLabel,
            'store_name'     => $branch->store->name,
        ]);
    }
}
