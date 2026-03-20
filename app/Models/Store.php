<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'logo_path',
        'address',
        'phone',
        'status',
        'excel_col_barcode',
        'excel_col_name',
        'excel_col_price',
        'retail_label',
        'show_wholesale',
        'wholesale_label',
        'wholesale_discount',
        'scan_bg_color',
        'scan_accent_color',
        'scan_secondary_color',
        'scan_card_style',
        'scan_font_size',
        'scan_show_logo',
        'scan_header_text',
        'scan_show_store_name',
        'scan_show_branch_name',
        'scan_wholesale_card_color',
    ];

    // --- Relaciones ---

    /** Usuarios que pertenecen a este comercio (dueño + empleados) */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /** Sucursales del comercio */
    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    /** Catálogo de productos */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /** Suscripción activa (puede haber una sola vigente) */
    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    /** Listas de precios del comercio */
    public function priceLists(): HasMany
    {
        return $this->hasMany(PriceList::class)->orderBy('sort_order');
    }

    /** Historial de importaciones */
    public function productImports(): HasMany
    {
        return $this->hasMany(ProductImport::class);
    }

    /** Perfiles de importación del comercio */
    public function importProfiles(): HasMany
    {
        return $this->hasMany(ImportProfile::class);
    }

    // --- Helpers ---

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function hasActiveSubscription(): bool
    {
        $sub = $this->subscription;
        if (! $sub) {
            return false;
        }

        return in_array($sub->status, ['trial', 'active']);
    }
}
