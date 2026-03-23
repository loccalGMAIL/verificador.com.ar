<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;

class HCaptcha implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            $fail('Por favor completá la verificación de seguridad.');
            return;
        }

        try {
            $response = Http::asForm()->post('https://api.hcaptcha.com/siteverify', [
                'secret'   => config('services.hcaptcha.secret'),
                'response' => $value,
                'remoteip' => request()->ip(),
                'sitekey'  => config('services.hcaptcha.site_key'),
            ]);

            if (! $response->successful() || ! $response->json('success')) {
                $fail('La verificación de seguridad falló. Por favor intentá de nuevo.');
            }
        } catch (\Exception $e) {
            $fail('No se pudo verificar la seguridad. Por favor intentá de nuevo.');
        }
    }
}
