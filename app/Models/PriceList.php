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
        'base_price_list_id',
        'adjustment_pct',
    ];

    protected function casts(): array
    {
        return [
            'is_default'     => 'boolean',
            'active'         => 'boolean',
            'adjustment_pct' => 'decimal:2',
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

    /** Lista base de la que deriva esta lista (null si es manual) */
    public function baseList(): BelongsTo
    {
        return $this->belongsTo(PriceList::class, 'base_price_list_id');
    }

    /** Listas que derivan de esta lista */
    public function derivedLists(): HasMany
    {
        return $this->hasMany(PriceList::class, 'base_price_list_id');
    }

    // --- Helpers ---

    /** Indica si esta lista se calcula automáticamente desde otra */
    public function isCalculated(): bool
    {
        return ! is_null($this->base_price_list_id);
    }

    /**
     * Aplica el ajuste porcentual a un precio base.
     * Ej: adjustment_pct = -20 → precio × 0.80
     *     adjustment_pct = +15 → precio × 1.15
     */
    public function calculateAdjustedPrice(float $basePrice): float
    {
        if (is_null($this->adjustment_pct) || $basePrice <= 0) {
            return $basePrice;
        }

        return round($basePrice * (1 + (float) $this->adjustment_pct / 100), 2);
    }

    public function isDefault(): bool
    {
        return (bool) $this->is_default;
    }

    /** Cuenta cuántos productos tienen precio cargado en esta lista */
    public function productCount(): int
    {
        return $this->productPrices()->count();
    }

    /** Etiqueta legible del ajuste porcentual. Ej: "+15.00%" o "-20.00%" */
    public function adjustmentLabel(): string
    {
        if (is_null($this->adjustment_pct)) {
            return '';
        }

        $pct = (float) $this->adjustment_pct;

        return $pct >= 0
            ? '+' . number_format($pct, 2) . '%'
            : number_format($pct, 2) . '%';
    }
}
