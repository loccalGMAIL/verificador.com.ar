<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductPrice extends Model
{
    protected $fillable = [
        'product_id',
        'price_list_id',
        'price_ars',
        'price_usd',
        'currency_default',
    ];

    protected function casts(): array
    {
        return [
            'price_ars' => 'decimal:2',
            'price_usd' => 'decimal:2',
        ];
    }

    // --- Relaciones ---

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function priceList(): BelongsTo
    {
        return $this->belongsTo(PriceList::class);
    }

    // --- Helpers ---

    /** Precio formateado según la moneda por defecto de esta entrada */
    public function formattedPrice(): string
    {
        if ($this->currency_default === 'USD') {
            return 'U$S ' . number_format((float) $this->price_usd, 2, ',', '.');
        }

        return '$ ' . number_format((float) $this->price_ars, 2, ',', '.');
    }

    /** Precio numérico según la moneda por defecto */
    public function numericPrice(): float
    {
        return $this->currency_default === 'USD'
            ? (float) $this->price_usd
            : (float) $this->price_ars;
    }
}
