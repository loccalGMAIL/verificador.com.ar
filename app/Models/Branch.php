<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'name',
        'address',
        'qr_token',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    /** Genera el token UUID antes de crear */
    protected static function booted(): void
    {
        static::creating(function (Branch $branch) {
            if (empty($branch->qr_token)) {
                $branch->qr_token = (string) Str::uuid();
            }
        });
    }

    // --- Relaciones ---

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    // --- Helpers ---

    /** URL pública que se codifica en el QR */
    public function scanUrl(): string
    {
        return route('scan.index', ['token' => $this->qr_token]);
    }
}
