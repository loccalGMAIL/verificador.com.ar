<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Services\MercadoPagoService;
use App\Services\SubscriptionPaymentSyncService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class MercadoPagoController extends Controller
{
    public function __invoke(Request $request, MercadoPagoService $mp, SubscriptionPaymentSyncService $sync): Response
    {
        $type = $request->input('type') ?? $request->query('type') ?? $request->input('topic');
        $mpId = $request->input('data.id') ?? $request->query('data.id');

        // 0. Log diagnóstico de toda notificación entrante (auditable en producción)
        Log::info('MP webhook recibido', [
            'type' => $type,
            'data_id' => $mpId,
            'query' => $request->query(),
        ]);

        // 1. Verificar firma
        if (! $mp->verifyWebhookSignature($request)) {
            Log::warning('MP webhook: firma inválida', [
                'ip' => $request->ip(),
                'type' => $type,
                'data_id' => $mpId,
                'has_signature_header' => $request->hasHeader('x-signature'),
                'has_request_id_header' => $request->hasHeader('x-request-id'),
            ]);

            return response('Unauthorized', 401);
        }

        // 2. Solo procesar eventos de suscripción
        if (! in_array($type, ['subscription_preapproval', 'subscription_authorized_payment'])) {
            return response('OK', 200);
        }

        // 3. Validar ID del evento (body o query string)
        if (! $mpId) {
            return response('Bad Request', 400);
        }

        // 4. Bifurcar según tipo
        if ($type === 'subscription_authorized_payment') {
            return $this->handleAuthorizedPayment($mp, $sync, $mpId);
        }

        return $this->handlePreapproval($mp, $sync, $mpId);
    }

    private function handlePreapproval(MercadoPagoService $mp, SubscriptionPaymentSyncService $sync, string $mpSubscriptionId): Response
    {
        // Buscar suscripción local
        $subscription = Subscription::where('mp_subscription_id', $mpSubscriptionId)->first();

        if (! $subscription) {
            Log::info('MP webhook: preapproval desconocida', ['mp_id' => $mpSubscriptionId]);

            return response('OK', 200);
        }

        // Obtener estado actual desde MP y sincronizar
        try {
            $sync->syncPreapprovalStatus($subscription, $mp->getPreapproval($mpSubscriptionId));
        } catch (\Exception $e) {
            Log::error('MP webhook: falló al obtener preapproval', [
                'mp_id' => $mpSubscriptionId,
                'error' => $e->getMessage(),
            ]);

            return response('Error', 500);
        }

        return response('OK', 200);
    }

    private function handleAuthorizedPayment(MercadoPagoService $mp, SubscriptionPaymentSyncService $sync, string $mpPaymentId): Response
    {
        // Obtener datos del pago desde MP
        try {
            $paymentData = $mp->getAuthorizedPayment($mpPaymentId);
        } catch (\Exception $e) {
            Log::error('MP webhook: falló al obtener authorized_payment', [
                'mp_payment_id' => $mpPaymentId,
                'error' => $e->getMessage(),
            ]);

            return response('Error', 500);
        }

        // Encontrar la suscripción local via preapproval_id
        $preapprovalId = $paymentData['preapproval_id'] ?? null;
        $subscription = Subscription::where('mp_subscription_id', $preapprovalId)->first();

        if (! $subscription) {
            Log::info('MP webhook: authorized_payment sin suscripción conocida', [
                'mp_payment_id' => $mpPaymentId,
                'preapproval_id' => $preapprovalId,
            ]);

            return response('OK', 200);
        }

        $sync->syncPayment($subscription, $paymentData, $mpPaymentId);

        return response('OK', 200);
    }
}
