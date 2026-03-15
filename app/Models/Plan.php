<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price_usd',
        'max_products',
        'description',
        'featured',
        'active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price_usd'   => 'decimal:2',
            'featured'    => 'boolean',
            'active'      => 'boolean',
        ];
    }

    // --- Relaciones ---

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    // --- Helpers ---

    public function hasProductLimit(): bool
    {
        return ! is_null($this->max_products);
    }

    public function maxProductsLabel(): string
    {
        return $this->max_products
            ? number_format($this->max_products)
            : 'Ilimitados';
    }
}
