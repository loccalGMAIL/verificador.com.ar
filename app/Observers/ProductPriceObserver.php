<?php

namespace App\Observers;

use App\Models\ProductPrice;
use App\Services\PriceCalculationService;

class ProductPriceObserver
{
    public function __construct(
        private readonly PriceCalculationService $calculator
    ) {}

    /**
     * Cuando se guarda un precio (crear o actualizar), recalcula
     * todos los precios derivados de la misma lista base.
     */
    public function saved(ProductPrice $productPrice): void
    {
        $this->calculator->recalculateForBasePrice($productPrice);
    }

    /**
     * Cuando se elimina un precio de la lista base, elimina también
     * los precios derivados correspondientes.
     */
    public function deleted(ProductPrice $productPrice): void
    {
        $this->calculator->deleteForBasePrice($productPrice);
    }
}
