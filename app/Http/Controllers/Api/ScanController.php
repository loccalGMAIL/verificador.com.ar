<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductSearch;
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

        $store = $branch->store;

        if (! $store->hasActiveSubscription()) {
            return response()->json(['found' => false, 'error' => 'Servicio no disponible.'], 403);
        }

        $product = Product::where('store_id', $branch->store_id)
            ->where('barcode', $barcode)
            ->where('active', true)
            ->first();

        if (! $product) {
            try {
                ProductSearch::create([
                    'branch_id'  => $branch->id,
                    'product_id' => null,
                    'barcode'    => $barcode,
                    'found'      => false,
                ]);
            } catch (\Throwable) {}

            return response()->json(['found' => false]);
        }

        try {
            ProductSearch::create([
                'branch_id'  => $branch->id,
                'product_id' => $product->id,
                'barcode'    => $barcode,
                'found'      => true,
            ]);
        } catch (\Throwable) {}

        $retailPrice = (float) $product->price;

        $response = [
            'found'          => true,
            'name'           => $product->name,
            'store_name'     => $store->name,
            'retail_label'   => $store->retail_label  ?? 'Precio',
            'retail_price'   => $retailPrice,
            'show_wholesale' => (bool) $store->show_wholesale,
        ];

        if ($store->show_wholesale) {
            $discount = (float) ($store->wholesale_discount ?? 0);
            $response['wholesale_label'] = $store->wholesale_label ?? 'Mayorista';
            $response['wholesale_price'] = round($retailPrice * (1 - $discount / 100), 2);
        }

        return response()->json($response);
    }
}
