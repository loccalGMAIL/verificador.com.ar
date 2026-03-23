<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Services\MercadoPagoService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class MercadoPagoController extends Controller
{
    public function __invoke(Request $request, MercadoPagoService $mp): Response
    {
        // 0. Log diagnóstico (quitar en producción)
        Log::debug('MP webhook recibido', [
            'type'    => $request->input('type') ?? $request->query('type') ?? $request->input('topic'),
            'data_id' => $request->input('data.id') ?? $request->query('data.id'),
            'query'   => $request->query(),
            'body'    => $request->all(),
        ]);

        // 1. Verificar firma
        if (! $mp->verifyWebhookSignature($request)) {
            Log::warning('MP webhook: firma inválida', ['ip' => $request->ip()]);

            return response('Unauthorized', 401);
        }

        // 2. Solo procesar eventos de suscripción
        $type = $request->input('type') ?? $request->query('type') ?? $request->input('topic');

        if (! in_array($type, ['subscription_preapproval', 'subscription_authorized_payment'])) {
            return response('OK', 200);
        }

        // 3. Extraer ID del evento (body o query string)
        $mpId = $request->input('data.id') ?? $request->query('data.id');

        if (! $mpId) {
            return response('Bad Request', 400);
        }

        // 4. Bifurcar según tipo
        if ($type === 'subscription_authorized_payment') {
            return $this->handleAuthorizedPayment($mp, $mpId);
        }

        return $this->handlePreapproval($mp, $mpId);
    }

    private function handlePreapproval(MercadoPagoService $mp, string $mpSubscriptionId): Response
    {
        // Buscar suscripción local
        $subscription = Subscription::where('mp_subscription_id', $mpSubscriptionId)->first();

        if (! $subscription) {
            Log::info('MP webhook: preapproval desconocida', ['mp_id' => $mpSubscriptionId]);

            return response('OK', 200);
        }

        // Obtener estado actual desde MP
        try {
            $mpData = $mp->getPreapproval($mpSubscriptionId);
        } catch (\Exception $e) {
            Log::error('MP webhook: falló al obtener preapproval', [
                'mp_id' => $mpSubscriptionId,
                'error' => $e->getMessage(),
            ]);

            return response('Error', 500);
        }

        $mpStatus  = $mpData['status'] ?? null;
        $mpPayerId = $mpData['payer_id'] ?? null;

        $localStatus = match ($mpStatus) {
            'authorized' => 'active',
            'paused'     => 'suspended',
            'cancelled'  => 'cancelled',
            default      => null,
        };

        $updates = [];

        if ($localStatus && $subscription->status !== $localStatus) {
            $updates['status'] = $localStatus;

            if ($localStatus === 'active' && ! $subscription->starts_at) {
                $updates['starts_at'] = now();
            }
        }

        if ($mpPayerId && ! $subscription->mp_payer_id) {
            $updates['mp_payer_id'] = $mpPayerId;
        }

        if (! empty($mpData['payer_email']) && ! $subscription->mp_payer_email) {
            $updates['mp_payer_email'] = $mpData['payer_email'];
        }

        if (! empty($updates)) {
            $subscription->update($updates);

            Log::info('MP webhook: suscripción actualizada', [
                'subscription_id' => $subscription->id,
                'updates'         => array_keys($updates),
            ]);
        }

        return response('OK', 200);
    }

    private function handleAuthorizedPayment(MercadoPagoService $mp, string $mpPaymentId): Response
    {
        // Obtener datos del pago desde MP
        try {
            $paymentData = $mp->getAuthorizedPayment($mpPaymentId);
        } catch (\Exception $e) {
            Log::error('MP webhook: falló al obtener authorized_payment', [
                'mp_payment_id' => $mpPaymentId,
                'error'         => $e->getMessage(),
            ]);

            return response('Error', 500);
        }

        // Encontrar la suscripción local via preapproval_id
        $preapprovalId = $paymentData['preapproval_id'] ?? null;
        $subscription  = Subscription::where('mp_subscription_id', $preapprovalId)->first();

        if (! $subscription) {
            Log::info('MP webhook: authorized_payment sin suscripción conocida', [
                'mp_payment_id' => $mpPaymentId,
                'preapproval_id' => $preapprovalId,
            ]);

            return response('OK', 200);
        }

        // Crear o actualizar (idempotencia por mp_payment_id único)
        SubscriptionPayment::updateOrCreate(
            ['mp_payment_id' => $mpPaymentId],
            [
                'subscription_id' => $subscription->id,
                'amount'          => $paymentData['transaction_amount'] ?? 0,
                'currency'        => $paymentData['currency_id'] ?? 'ARS',
                'status'          => $paymentData['status'] ?? 'processed',
                'paid_at'         => isset($paymentData['date_approved'])
                                        ? Carbon::parse($paymentData['date_approved'])
                                        : null,
            ]
        );

        Log::info('MP webhook: pago registrado', [
            'subscription_id' => $subscription->id,
            'mp_payment_id'   => $mpPaymentId,
        ]);

        return response('OK', 200);
    }
}
