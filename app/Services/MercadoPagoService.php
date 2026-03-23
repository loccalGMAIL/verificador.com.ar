<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MercadoPagoService
{
    private const BASE_URL = 'https://api.mercadopago.com';

    private string $token;

    public function __construct()
    {
        $this->token = config('mercadopago.access_token');
    }

    // ── Preapprovals (suscripciones sin plan asociado — pago pendiente) ───────

    /**
     * Crea una preaprobación para un usuario/tienda (flujo "sin plan, pago pendiente").
     * Retorna ['id' => string, 'init_point' => string].
     *
     * El usuario es redirigido a init_point para ingresar su medio de pago en MP.
     * Una vez autorizado, MP envía un webhook a notification_url.
     */
    public function createPreapproval(Plan $plan, Store $store, string $payerEmail): array
    {
        $response = Http::withToken($this->token)
            ->post(self::BASE_URL . '/preapproval', [
                'reason'             => 'Suscripción verificador.com.ar — ' . $plan->name,
                'payer_email'        => $payerEmail,
                'external_reference' => 'store_' . $store->id . '_plan_' . $plan->id,
                'back_url'           => config('mercadopago.back_url'),
                'notification_url'   => config('mercadopago.notification_url'),
                'auto_recurring'     => [
                    'frequency'          => 1,
                    'frequency_type'     => 'months',
                    'transaction_amount' => (float) $plan->price_ars,
                    'currency_id'        => 'ARS',
                ],
                'status' => 'pending',
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException(
                'MercadoPago createPreapproval falló: ' . $response->body()
            );
        }

        $data = $response->json();

        return [
            'id'         => $data['id'],
            'init_point' => $data['init_point'],
        ];
    }

    /**
     * Obtiene el estado actual de una preaprobación desde MercadoPago.
     */
    public function getPreapproval(string $mpSubscriptionId): array
    {
        $response = Http::withToken($this->token)
            ->get(self::BASE_URL . "/preapproval/{$mpSubscriptionId}");

        if (! $response->successful()) {
            throw new \RuntimeException(
                'MercadoPago getPreapproval falló: ' . $response->body()
            );
        }

        return $response->json();
    }

    /**
     * Cancela una preaprobación de usuario.
     * La documentación de MP especifica PUT (no PATCH) para cambios de status.
     */
    public function cancelPreapproval(string $mpSubscriptionId): void
    {
        $response = Http::withToken($this->token)
            ->put(self::BASE_URL . "/preapproval/{$mpSubscriptionId}", [
                'status' => 'cancelled',
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException(
                'MercadoPago cancelPreapproval falló: ' . $response->body()
            );
        }
    }

    /**
     * Obtiene los datos de un pago recurrente autorizado.
     */
    public function getAuthorizedPayment(string $mpPaymentId): array
    {
        $response = Http::withToken($this->token)
            ->get(self::BASE_URL . "/authorized_payments/{$mpPaymentId}");

        if (! $response->successful()) {
            throw new \RuntimeException(
                'MercadoPago getAuthorizedPayment falló: ' . $response->body()
            );
        }

        return $response->json();
    }

    // ── Seguridad de Webhooks ─────────────────────────────────────────────────

    /**
     * Verifica la firma HMAC-SHA256 del webhook de MercadoPago.
     *
     * Header x-signature formato: ts=<timestamp>,v1=<hmac>
     * Template firmado (según documentación oficial):
     *   id:[data.id_url];request-id:[x-request-id_header];ts:[ts_header];
     *
     * IMPORTANTE: data.id proviene de los query params de la URL (?data.id=xxx),
     * no del JSON body. Los parámetros ausentes se omiten del template.
     */
    public function verifyWebhookSignature(Request $request): bool
    {
        $secret = config('mercadopago.webhook_secret');

        // Si no hay secret configurado, saltear verificación (solo en local/dev)
        if (empty($secret)) {
            return true;
        }

        $xSignature = $request->header('x-signature');
        $xRequestId = $request->header('x-request-id', '');

        if (! $xSignature) {
            return false;
        }

        // Parsear ts y v1 del header
        $ts = null;
        $v1 = null;

        foreach (explode(',', $xSignature) as $part) {
            [$key, $value] = array_pad(explode('=', $part, 2), 2, '');
            if ($key === 'ts') {
                $ts = $value;
            } elseif ($key === 'v1') {
                $v1 = $value;
            }
        }

        if (! $ts || ! $v1) {
            return false;
        }

        // data.id viene del query string (sufijo _url en la documentación de MP)
        $dataId = $request->query('data.id', '');

        // Construir template omitiendo valores vacíos (según nota de la documentación)
        $parts = [];
        if ($dataId !== '') {
            $parts[] = 'id:' . strtolower($dataId);
        }
        if ($xRequestId !== '') {
            $parts[] = 'request-id:' . $xRequestId;
        }
        $parts[] = 'ts:' . $ts;

        $signedTemplate = implode(';', $parts) . ';';

        $expected = hash_hmac('sha256', $signedTemplate, $secret);

        return hash_equals($expected, $v1);
    }
}
