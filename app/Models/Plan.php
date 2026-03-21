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
        'price_ars',
        'max_products',
        'max_branches',
        'max_price_lists',
        'description',
        'featured',
        'active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price_usd'    => 'decimal:2',
            'price_ars'    => 'decimal:2',
            'featured'     => 'boolean',
            'active'       => 'boolean',
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

    public function hasBranchLimit(): bool
    {
        return ! is_null($this->max_branches);
    }

    public function maxProductsLabel(): string
    {
        return $this->max_products
            ? number_format($this->max_products)
            : 'Ilimitados';
    }

    public function maxBranchesLabel(): string
    {
        return $this->max_branches
            ? number_format($this->max_branches)
            : 'Ilimitadas';
    }

    public function hasPriceListLimit(): bool
    {
        return ! is_null($this->max_price_lists);
    }

    public function maxPriceListsLabel(): string
    {
        return $this->max_price_lists
            ? number_format($this->max_price_lists)
            : 'Ilimitadas';
    }

    public function formattedPriceArs(): string
    {
        if (is_null($this->price_ars)) {
            return '—';
        }

        return '$ ' . number_format((float) $this->price_ars, 0, ',', '.');
    }
}
