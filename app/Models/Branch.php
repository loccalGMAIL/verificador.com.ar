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
        'qr_scheme',
        'qr_layout',
        'qr_headline',
        'qr_instruction',
        'qr_show_logo',
        'qr_show_branch',
        'qr_logo_position',
        'qr_qr_size',
        'qr_headline_size',
        'qr_instr_size',
        'qr_logo_size',
    ];

    protected function casts(): array
    {
        return [
            'active'         => 'boolean',
            'qr_show_logo'   => 'boolean',
            'qr_show_branch' => 'boolean',
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
