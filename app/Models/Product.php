<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'name',
        'barcode',
        'description',
        'price_ars',       // Legacy — se mantiene como fallback
        'price_usd',       // Legacy — se mantiene como fallback
        'currency_default',
        'image_path',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'price_ars' => 'decimal:2',
            'price_usd' => 'decimal:2',
            'active'    => 'boolean',
        ];
    }

    // --- Relaciones ---

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class);
    }

    // --- Helpers ---

    /** Precio de este producto en una lista específica (null si no tiene precio cargado) */
    public function priceForList(PriceList $list): ?ProductPrice
    {
        return $this->prices->firstWhere('price_list_id', $list->id);
    }

    /** Precio legado formateado (para compatibilidad con código existente) */
    public function formattedPrice(): string
    {
        if ($this->currency_default === 'USD') {
            return 'U$S ' . number_format((float) $this->price_usd, 2, ',', '.');
        }

        return '$ ' . number_format((float) $this->price_ars, 2, ',', '.');
    }
}
