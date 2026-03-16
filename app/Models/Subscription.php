<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'plan_id',
        'status',
        'trial_ends_at',
        'starts_at',
        'ends_at',
        'mp_subscription_id',
        'mp_payer_id',
    ];

    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'datetime',
            'starts_at'     => 'datetime',
            'ends_at'       => 'datetime',
        ];
    }

    // --- Relaciones ---

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    // --- Estado ---

    public function isOnTrial(): bool
    {
        return $this->status === 'trial'
            && $this->trial_ends_at
            && $this->trial_ends_at->isFuture();
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * La suscripción expiró y no tiene acceso al dashboard.
     * Cubre: trial vencido, suspendida o cancelada sin estar activa.
     */
    public function isExpired(): bool
    {
        if ($this->isActive()) {
            return false;
        }

        if ($this->isOnTrial()) {
            return false;
        }

        return true;
    }

    /**
     * El usuario tiene acceso total sin restricciones de límite.
     * Durante el trial siempre es acceso full (equivale a Business).
     */
    public function hasFullAccess(): bool
    {
        return $this->isOnTrial();
    }

    // --- Helpers ---

    public function trialDaysRemaining(): int
    {
        if (! $this->isOnTrial()) {
            return 0;
        }

        return max(0, (int) now()->diffInDays($this->trial_ends_at, false));
    }

    /**
     * Etiqueta del estado para mostrar en UI.
     */
    public function statusLabel(): string
    {
        return match ($this->status) {
            'trial'     => 'Trial',
            'active'    => 'Activa',
            'suspended' => 'Suspendida',
            'cancelled' => 'Cancelada',
            default     => ucfirst($this->status),
        };
    }
}
