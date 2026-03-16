<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Crea una lista "General" (is_default=true) por cada store existente
     * y migra los precios actuales de los productos a esa lista.
     */
    public function up(): void
    {
        $stores = DB::table('stores')->get();

        foreach ($stores as $store) {
            // Crear lista General para este store
            $priceListId = DB::table('price_lists')->insertGetId([
                'store_id'    => $store->id,
                'name'        => 'General',
                'description' => 'Lista de precios principal',
                'is_default'  => true,
                'active'      => true,
                'sort_order'  => 0,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            // Migrar precios de cada producto de este store
            $products = DB::table('products')
                ->where('store_id', $store->id)
                ->whereNotNull('price_ars')
                ->orWhere(fn ($q) => $q->where('store_id', $store->id)->whereNotNull('price_usd'))
                ->get();

            foreach ($products as $product) {
                DB::table('product_prices')->insertOrIgnore([
                    'product_id'       => $product->id,
                    'price_list_id'    => $priceListId,
                    'price_ars'        => $product->price_ars,
                    'price_usd'        => $product->price_usd,
                    'currency_default' => $product->currency_default ?? 'ARS',
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Eliminar todas las listas y precios generados (cascadeOnDelete se encarga de product_prices)
        DB::table('price_lists')->where('is_default', true)->delete();
    }
};
