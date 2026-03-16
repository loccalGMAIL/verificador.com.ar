<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImport extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'user_id',
        'file_name',
        'mapping',
        'price_list_id',
        'import_profile_id',
        'status',
        'rows_total',
        'rows_ok',
        'rows_error',
        'error_log',
    ];

    protected function casts(): array
    {
        return [
            'mapping'   => 'array',
            'error_log' => 'array',
        ];
    }

    // --- Relaciones ---

    public function priceList(): BelongsTo
    {
        return $this->belongsTo(PriceList::class);
    }

    public function importProfile(): BelongsTo
    {
        return $this->belongsTo(ImportProfile::class);
    }


    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // --- Helpers ---

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function hasFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function successRate(): float
    {
        if ($this->rows_total === 0) {
            return 0.0;
        }

        return round(($this->rows_ok / $this->rows_total) * 100, 1);
    }
}
