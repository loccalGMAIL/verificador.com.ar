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
     *
     * Devuelve el producto con sus precios en TODAS las listas activas del comercio.
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

        // Todas las listas activas del comercio con el precio de este producto
        $priceLists = $branch->store
            ->priceLists()
            ->where('active', true)
            ->with(['productPrices' => fn ($q) => $q->where('product_id', $product->id)])
            ->orderBy('sort_order')
            ->get();

        $prices = $priceLists->map(function ($list) {
            $pp = $list->productPrices->first();

            if (! $pp) {
                return [
                    'list_name' => $list->name,
                    'available' => false,
                    'price'     => null,
                ];
            }

            return [
                'list_name' => $list->name,
                'available' => true,
                'price'     => $pp->formattedPrice(),
            ];
        });

        return response()->json([
            'found'      => true,
            'name'       => $product->name,
            'store_name' => $branch->store->name,
            'prices'     => $prices,
        ]);
    }
}
