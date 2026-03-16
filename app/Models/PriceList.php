<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PriceList extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'name',
        'description',
        'is_default',
        'active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'active'     => 'boolean',
        ];
    }

    // --- Relaciones ---

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function productPrices(): HasMany
    {
        return $this->hasMany(ProductPrice::class);
    }

    // --- Helpers ---

    public function isDefault(): bool
    {
        return (bool) $this->is_default;
    }

    /** Cuenta cuántos productos tienen precio cargado en esta lista */
    public function productCount(): int
    {
        return $this->productPrices()->count();
    }
}
