<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'name',
        'barcode',
        'description',
        'price_ars',
        'price_usd',
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

    // --- Helpers ---

    /** Devuelve el precio formateado en la moneda por defecto del producto */
    public function formattedPrice(): string
    {
        if ($this->currency_default === 'USD') {
            return 'U$S ' . number_format((float) $this->price_usd, 2, ',', '.');
        }

        return '$ ' . number_format((float) $this->price_ars, 2, ',', '.');
    }
}
