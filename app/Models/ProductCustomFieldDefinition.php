<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductCustomFieldDefinition extends Model
{
    protected $fillable = [
        'store_id',
        'label',
        'excel_column',
        'visible_on_scan',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'visible_on_scan' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
