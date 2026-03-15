<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageView extends Model
{
    protected $fillable = [
        'path',
        'referer',
        'referer_domain',
        'referer_category',
        'ip_hash',
        'user_agent',
    ];

    // -------------------------------------------------------
    // Helpers de categorización (usados por el middleware)
    // -------------------------------------------------------

    /**
     * Extrae el dominio de una URL de referer.
     * Devuelve null si la URL es inválida o vacía.
     */
    public static function extractDomain(?string $url): ?string
    {
        if (blank($url)) {
            return null;
        }

        $host = parse_url($url, PHP_URL_HOST);
        if (! $host) {
            return null;
        }

        // Eliminar "www." para normalizar
        return preg_replace('/^www\./i', '', strtolower($host));
    }

    /**
     * Clasifica un dominio en una de las categorías:
     * direct | search | social | other
     */
    public static function categorize(?string $domain): string
    {
        if (blank($domain)) {
            return 'direct';
        }

        $search = ['google.', 'bing.', 'yahoo.', 'duckduckgo.', 'baidu.', 'yandex.', 'ecosia.'];
        $social = ['facebook.', 'instagram.', 'twitter.', 'x.com', 'linkedin.', 'tiktok.', 'youtube.', 'pinterest.', 'reddit.', 'whatsapp.'];

        foreach ($search as $s) {
            if (str_contains($domain, $s)) {
                return 'search';
            }
        }
        foreach ($social as $s) {
            if (str_contains($domain, $s)) {
                return 'social';
            }
        }

        return 'other';
    }
}
