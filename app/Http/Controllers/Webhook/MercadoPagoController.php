<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Services\MercadoPagoService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class MercadoPagoController extends Controller
{
    public function __invoke(Request $request, MercadoPagoService $mp): Response
    {
        // 1. Verificar firma
        if (! $mp->verifyWebhookSignature($request)) {
            Log::warning('MP webhook: firma inválida', ['ip' => $request->ip()]);

            return response('Unauthorized', 401);
        }

        // 2. Solo procesar eventos de suscripción
        // MP puede enviar el type en el body o en el query string
        $type = $request->input('type') ?? $request->query('type') ?? $request->input('topic');

        if ($type !== 'subscription_preapproval') {
            return response('OK', 200);
        }

        // 3. Extraer ID de la preaprobación (body o query string)
        $mpSubscriptionId = $request->input('data.id') ?? $request->query('data.id');

        if (! $mpSubscriptionId) {
            return response('Bad Request', 400);
        }

        // 4. Buscar suscripción local
        $subscription = Subscription::where('mp_subscription_id', $mpSubscriptionId)->first();

        if (! $subscription) {
            Log::info('MP webhook: preapproval desconocida', ['mp_id' => $mpSubscriptionId]);

            return response('OK', 200);
        }

        // 5. Obtener estado actual desde MP (no confiar solo en el payload del webhook)
        try {
            $mpData = $mp->getPreapproval($mpSubscriptionId);
        } catch (\Exception $e) {
            Log::error('MP webhook: falló al obtener preapproval', [
                'mp_id' => $mpSubscriptionId,
                'error' => $e->getMessage(),
            ]);

            // Retornar 500 para que MP reintente
            return response('Error', 500);
        }

        // 6. Mapear status de MP al status local
        $mpStatus    = $mpData['status'] ?? null;
        $mpPayerId   = $mpData['payer_id'] ?? null;

        $localStatus = match ($mpStatus) {
            'authorized' => 'active',
            'paused'     => 'suspended',
            'cancelled'  => 'cancelled',
            default      => null, // 'pending' u otros: no cambiar
        };

        // 7. Actualizar solo si cambió (idempotencia)
        if ($localStatus && $subscription->status !== $localStatus) {
            $updates = ['status' => $localStatus];

            if ($localStatus === 'active' && ! $subscription->starts_at) {
                $updates['starts_at'] = now();
            }

            if ($mpPayerId && ! $subscription->mp_payer_id) {
                $updates['mp_payer_id'] = $mpPayerId;
            }

            $subscription->update($updates);

            Log::info('MP webhook: suscripción actualizada', [
                'subscription_id' => $subscription->id,
                'status'          => $localStatus,
            ]);
        }

        return response('OK', 200);
    }
}
