<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImportProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'name',
        'description',
        'header_mapping',
    ];

    protected function casts(): array
    {
        return [
            'header_mapping' => 'array',
        ];
    }

    // --- Relaciones ---

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function productImports(): HasMany
    {
        return $this->hasMany(ProductImport::class);
    }

    // --- Helpers ---

    /**
     * Recibe un array de nombres de encabezados del archivo y devuelve
     * el mapping { field_key => column_index } para usar en el job.
     *
     * header_mapping guardado: { "barcode": "codigo_barras", "price_list_1_ars": "precio" }
     * headers del archivo:     ["codigo_barras", "nombre", "precio"]
     * resultado:               { "barcode": 0, "name": null, "price_list_1_ars": 2 }
     */
    public function resolveMapping(array $headers): array
    {
        $normalizedHeaders = array_map(
            fn ($h) => strtolower(trim((string) $h)),
            $headers
        );

        $result = [];

        foreach ($this->header_mapping as $field => $expectedHeader) {
            $normalized = strtolower(trim((string) $expectedHeader));
            $index      = array_search($normalized, $normalizedHeaders, true);

            $result[$field] = $index !== false ? (int) $index : null;
        }

        return $result;
    }
}
