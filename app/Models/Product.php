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
        'price',
        'image_path',
        'custom_fields',
        'active',
        'source',
        'odoo_product_id',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'active' => 'boolean',
            'custom_fields' => 'array',
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

    /** Indica si el producto se sincroniza desde Odoo (nombre, código, precio y estado no se editan acá) */
    public function isManagedByOdoo(): bool
    {
        return $this->source === 'odoo';
    }

    public function formattedPrice(): string
    {
        return '$ '.number_format((float) $this->price, 2, ',', '.');
    }
}
