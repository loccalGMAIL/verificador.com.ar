<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionPayment extends Model
{
    protected $fillable = [
        'subscription_id',
        'mp_payment_id',
        'amount',
        'currency',
        'status',
        'paid_at',
        'notes',
        'debit_date',
        'status_detail',
    ];

    public function isManual(): bool
    {
        return $this->mp_payment_id === null;
    }

    protected $casts = [
        'paid_at' => 'datetime',
        'debit_date' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
