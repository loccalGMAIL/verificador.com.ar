<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionPayment extends Model
{
    public const METHOD_MERCADOPAGO = 'mercadopago';

    public const METHOD_TRANSFER = 'transfer';

    public const METHOD_CASH = 'cash';

    public const METHOD_OTHER = 'other';

    /**
     * Medios de pago que el admin puede registrar manualmente.
     *
     * @var array<string, string>
     */
    public const MANUAL_METHODS = [
        self::METHOD_TRANSFER => 'Transferencia',
        self::METHOD_CASH => 'Efectivo',
        self::METHOD_OTHER => 'Otro',
    ];

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
        'payment_method',
        'reference',
    ];

    public function isManual(): bool
    {
        return $this->mp_payment_id === null;
    }

    /**
     * Etiqueta legible del medio de pago. Los pagos manuales anteriores a
     * `payment_method` se muestran como "Manual".
     */
    public function paymentMethodLabel(): string
    {
        if (! $this->isManual()) {
            return 'MercadoPago';
        }

        return self::MANUAL_METHODS[$this->payment_method] ?? 'Manual';
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
