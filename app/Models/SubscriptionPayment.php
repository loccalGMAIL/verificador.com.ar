<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
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

    /**
     * Fecha a mostrar del cobro: `paid_at` o, para cobros acreditados sin ella, `debit_date`.
     */
    public function displayDate(): ?Carbon
    {
        return $this->paid_at ?? ($this->status === 'processed' ? $this->debit_date : null);
    }

    /**
     * Ordena del cobro más reciente al más antiguo según la fecha efectiva
     * (`paid_at`, luego `debit_date`, luego `created_at`).
     *
     * @param  Builder<SubscriptionPayment>  $query
     */
    public function scopeLatestFirst(Builder $query): void
    {
        $query->orderByRaw('COALESCE(paid_at, debit_date, created_at) DESC')
            ->orderByDesc('id');
    }
}
