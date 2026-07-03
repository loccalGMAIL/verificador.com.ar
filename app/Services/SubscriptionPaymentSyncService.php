<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class SubscriptionPaymentSyncService
{
    public function __construct(private MercadoPagoService $mp) {}

    /**
     * Sincroniza una suscripción completa contra MercadoPago:
     * estado de la preaprobación + todos sus pagos recurrentes
     * (incluidos rechazados/en recycling). Retorna la cantidad
     * de pagos sincronizados.
     */
    public function syncSubscription(Subscription $subscription): int
    {
        if (! $subscription->mp_subscription_id) {
            return 0;
        }

        $this->syncPreapprovalStatus($subscription, $this->mp->getPreapproval($subscription->mp_subscription_id));

        return $this->syncPayments($subscription);
    }

    /**
     * Trae todos los pagos recurrentes de la suscripción desde MP y los
     * registra localmente. Retorna la cantidad de pagos sincronizados.
     */
    public function syncPayments(Subscription $subscription): int
    {
        $synced = 0;

        foreach ($this->mp->searchAuthorizedPayments($subscription->mp_subscription_id) as $paymentData) {
            $mpPaymentId = $paymentData['id'] ?? null;

            if (! $mpPaymentId) {
                continue;
            }

            $this->syncPayment($subscription, $paymentData, (string) $mpPaymentId);
            $synced++;
        }

        return $synced;
    }

    /**
     * Actualiza el estado local de la suscripción según la preaprobación de MP.
     *
     * @param  array<string, mixed>  $mpData  Respuesta de GET /preapproval/{id}
     */
    public function syncPreapprovalStatus(Subscription $subscription, array $mpData): void
    {
        $localStatus = match ($mpData['status'] ?? null) {
            'authorized' => 'active',
            'paused' => 'suspended',
            'cancelled' => 'cancelled',
            default => null,
        };

        $updates = [];

        if ($localStatus && $subscription->status !== $localStatus) {
            $updates['status'] = $localStatus;

            if ($localStatus === 'active' && ! $subscription->starts_at) {
                $updates['starts_at'] = now();
            }

            if ($localStatus === 'active' && is_null($subscription->next_payment_date)) {
                $updates['next_payment_date'] = now()->addMonth();
            }
        }

        if (! empty($mpData['payer_id']) && ! $subscription->mp_payer_id) {
            $updates['mp_payer_id'] = $mpData['payer_id'];
        }

        if (! empty($mpData['payer_email']) && ! $subscription->mp_payer_email) {
            $updates['mp_payer_email'] = $mpData['payer_email'];
        }

        if (! empty($updates)) {
            $subscription->update($updates);

            Log::info('MP sync: suscripción actualizada', [
                'subscription_id' => $subscription->id,
                'updates' => array_keys($updates),
            ]);
        }
    }

    /**
     * Registra (o actualiza, idempotente por mp_payment_id) un pago recurrente.
     *
     * @param  array<string, mixed>  $paymentData  Respuesta de GET /authorized_payments/{id}
     */
    public function syncPayment(Subscription $subscription, array $paymentData, string $mpPaymentId): SubscriptionPayment
    {
        $paymentStatus = $paymentData['status'] ?? 'processed';

        $payment = SubscriptionPayment::updateOrCreate(
            ['mp_payment_id' => $mpPaymentId],
            [
                'subscription_id' => $subscription->id,
                'amount' => $paymentData['transaction_amount'] ?? 0,
                'currency' => $paymentData['currency_id'] ?? 'ARS',
                'status' => $paymentStatus,
                'paid_at' => isset($paymentData['date_approved'])
                                        ? Carbon::parse($paymentData['date_approved'])
                                        : null,
                'debit_date' => isset($paymentData['debit_date'])
                                        ? Carbon::parse($paymentData['debit_date'])
                                        : null,
                'status_detail' => $paymentData['status_detail'] ?? null,
            ]
        );

        if ($paymentStatus === 'processed' && isset($paymentData['debit_date'])) {
            $nextPaymentDate = Carbon::parse($paymentData['debit_date'])->addMonth();

            if (is_null($subscription->next_payment_date) || $nextPaymentDate->isAfter($subscription->next_payment_date)) {
                $subscription->update(['next_payment_date' => $nextPaymentDate]);
            }
        } elseif ($paymentStatus === 'recycling') {
            Log::warning('MP: pago en recycling, MP reintentará automáticamente', [
                'subscription_id' => $subscription->id,
                'mp_payment_id' => $mpPaymentId,
                'status_detail' => $paymentData['status_detail'] ?? null,
            ]);
        }

        Log::info('MP: pago registrado', [
            'subscription_id' => $subscription->id,
            'mp_payment_id' => $mpPaymentId,
            'status' => $paymentStatus,
        ]);

        return $payment;
    }
}
