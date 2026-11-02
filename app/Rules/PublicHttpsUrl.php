<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * URL https hacia un host público. Evita que el servidor haga requests
 * a direcciones internas (localhost, redes privadas, metadata de la nube).
 */
class PublicHttpsUrl implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $url = trim((string) $value);
        if (! preg_match('#^https?://#i', $url)) {
            $url = 'https://'.$url;
        }

        $parts = parse_url($url);
        $host = strtolower($parts['host'] ?? '');

        if (($parts['scheme'] ?? '') !== 'https' || $host === '' || ! str_contains($host, '.')) {
            $fail('Ingresá la URL https de tu Odoo, por ejemplo https://miempresa.odoo.com');

            return;
        }

        $ips = filter_var($host, FILTER_VALIDATE_IP) ? [$host] : (gethostbynamel($host) ?: []);

        foreach ($ips as $ip) {
            if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                $fail('La URL de Odoo debe apuntar a un servidor público.');

                return;
            }
        }
    }
}
