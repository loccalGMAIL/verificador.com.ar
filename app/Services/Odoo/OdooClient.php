<?php

namespace App\Services\Odoo;

use App\Models\OdooConnection;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Cliente mínimo de la API JSON-2 de Odoo 19+.
 *
 * Cada llamada es un POST a /json/2/{modelo}/{método} con los argumentos con nombre en el body,
 * autenticado con "Authorization: bearer <api_key>" y la base indicada en "X-Odoo-Database".
 */
class OdooClient
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $database,
        private readonly string $apiKey,
    ) {}

    public static function forConnection(OdooConnection $connection): self
    {
        return new self($connection->url, $connection->database, $connection->api_key);
    }

    /**
     * Llama un método de un modelo de Odoo.
     *
     * @param  array<string, mixed>  $params
     *
     * @throws OdooException
     */
    public function call(string $model, string $method, array $params = []): mixed
    {
        $url = rtrim($this->baseUrl, '/')."/json/2/{$model}/{$method}";

        try {
            $response = Http::withToken($this->apiKey, 'bearer')
                ->withHeaders(['X-Odoo-Database' => $this->database])
                ->acceptJson()
                ->asJson()
                ->timeout((int) config('services.odoo.timeout', 30))
                ->retry(2, 1000, fn ($exception) => $exception instanceof ConnectionException, throw: false)
                ->post($url, $params);
        } catch (ConnectionException $e) {
            throw new OdooException('No se pudo conectar con Odoo. Verificá la URL de tu instancia.', 0, $e);
        }

        if ($response->failed()) {
            throw new OdooException($this->errorMessage($response));
        }

        return $response->json();
    }

    /**
     * @param  array<int, mixed>  $domain
     * @param  array<int, string>  $fields
     * @param  array<string, mixed>  $context
     * @return array<int, array<string, mixed>>
     */
    public function searchRead(
        string $model,
        array $domain,
        array $fields,
        int $limit,
        int $offset = 0,
        ?string $order = null,
        array $context = [],
    ): array {
        $params = [
            'domain' => $domain,
            'fields' => $fields,
            'limit' => $limit,
            'offset' => $offset,
        ];

        if ($order !== null) {
            $params['order'] = $order;
        }

        if ($context !== []) {
            $params['context'] = $context;
        }

        $result = $this->call($model, 'search_read', $params);

        if (! is_array($result)) {
            throw new OdooException('Respuesta inesperada de Odoo al leer productos.');
        }

        return $result;
    }

    /**
     * Verifica credenciales y permiso de lectura sobre productos.
     * Devuelve la cantidad de productos con código de barras.
     *
     * @throws OdooException
     */
    public function testConnection(): int
    {
        $count = $this->call('product.product', 'search_count', [
            'domain' => [['barcode', '!=', false]],
        ]);

        if (! is_int($count)) {
            throw new OdooException('La URL no parece ser una instancia de Odoo 19 o superior.');
        }

        return $count;
    }

    private function errorMessage(Response $response): string
    {
        $detail = $response->json('message');
        $detail = is_string($detail) && $detail !== '' ? " ({$detail})" : '';

        return match (true) {
            $response->status() === 401 => 'Odoo rechazó la API key: es inválida o venció.'.$detail,
            $response->status() === 403 => 'El usuario de Odoo no tiene permiso para leer productos.'.$detail,
            $response->status() === 404 => 'No se encontró la API de Odoo en esa URL o la base de datos no existe. Se requiere Odoo 19 o superior.'.$detail,
            default => "Odoo respondió con error {$response->status()}.".$detail,
        };
    }
}
