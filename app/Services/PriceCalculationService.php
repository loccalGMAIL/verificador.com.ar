<?php

namespace App\Services;

use App\Models\PriceList;
use App\Models\ProductPrice;

class PriceCalculationService
{
    /**
     * Recalcula TODOS los precios de una lista derivada a partir de su lista base.
     * Útil para el botón "Recalcular" y al cambiar el porcentaje de ajuste.
     */
    public function recalculateForList(PriceList $derivedList): void
    {
        if (! $derivedList->isCalculated()) {
            return;
        }

        $basePrices = ProductPrice::where('price_list_id', $derivedList->base_price_list_id)->get();

        foreach ($basePrices as $basePrice) {
            $this->applyToProduct($basePrice, $derivedList);
        }
    }

    /**
     * Recalcula el precio de UN producto en todas las listas derivadas de su lista base.
     * Llamado por el observer al guardar/actualizar un ProductPrice.
     */
    public function recalculateForBasePrice(ProductPrice $basePrice): void
    {
        $derivedLists = PriceList::where('base_price_list_id', $basePrice->price_list_id)
            ->where('active', true)
            ->get();

        foreach ($derivedLists as $derivedList) {
            $this->applyToProduct($basePrice, $derivedList);
        }
    }

    /**
     * Elimina el precio en todas las listas derivadas cuando se borra el precio base.
     */
    public function deleteForBasePrice(ProductPrice $basePrice): void
    {
        $derivedListIds = PriceList::where('base_price_list_id', $basePrice->price_list_id)
            ->pluck('id');

        if ($derivedListIds->isEmpty()) {
            return;
        }

        ProductPrice::where('product_id', $basePrice->product_id)
            ->whereIn('price_list_id', $derivedListIds)
            ->delete();
    }

    // ── Privado ──────────────────────────────────────────────────────

    private function applyToProduct(ProductPrice $basePrice, PriceList $derivedList): void
    {
        $arsBase = (float) $basePrice->price_ars;
        $usdBase = (float) $basePrice->price_usd;

        $newArs = $arsBase > 0 ? $derivedList->calculateAdjustedPrice($arsBase) : null;
        $newUsd = $usdBase > 0 ? $derivedList->calculateAdjustedPrice($usdBase) : null;

        if ($newArs === null && $newUsd === null) {
            // Sin precios en la base → eliminar de la derivada
            ProductPrice::where('product_id', $basePrice->product_id)
                ->where('price_list_id', $derivedList->id)
                ->delete();
            return;
        }

        ProductPrice::updateOrCreate(
            [
                'product_id'    => $basePrice->product_id,
                'price_list_id' => $derivedList->id,
            ],
            [
                'price_ars'        => $newArs,
                'price_usd'        => $newUsd,
                'currency_default' => $basePrice->currency_default,
            ]
        );
    }
}
