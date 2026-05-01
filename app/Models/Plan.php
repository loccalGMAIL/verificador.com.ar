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
        'mp_preapproval_plan_id',
        'has_import_history',
        'has_basic_stats',
        'has_advanced_stats',
        'has_price_lists',
        'has_customization',
        'has_manual_search',
        'has_branches',
        'has_api',
    ];

    protected function casts(): array
    {
        return [
            'price_usd' => 'decimal:2',
            'price_ars' => 'decimal:2',
            'featured' => 'boolean',
            'active' => 'boolean',
            'has_import_history' => 'boolean',
            'has_basic_stats' => 'boolean',
            'has_advanced_stats' => 'boolean',
            'has_price_lists' => 'boolean',
            'has_customization' => 'boolean',
            'has_manual_search' => 'boolean',
            'has_branches' => 'boolean',
            'has_api' => 'boolean',
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

    public function isPaid(): bool
    {
        return $this->price_ars !== null && (float) $this->price_ars > 0;
    }

    public function formattedPriceArs(): string
    {
        if (is_null($this->price_ars)) {
            return '—';
        }

        return '$ '.number_format((float) $this->price_ars, 0, ',', '.');
    }

    public function hasFeature(string $feature): bool
    {
        $featureKey = 'has_'.ltrim($feature, 'has_');

        return (bool) $this->getAttribute($featureKey);
    }
}
