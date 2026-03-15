<?php

namespace App\Http\Middleware;

use App\Models\PageView;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogPageView
{
    /**
     * Paths públicos que queremos registrar.
     * Los que empiezan con /v/ se agregan dinámicamente abajo.
     */
    private const TRACKED_PATHS = ['/', '/register', '/login'];

    /**
     * Fragmentos de user-agent que indican bots conocidos.
     */
    private const BOT_SIGNATURES = [
        'bot', 'crawl', 'spider', 'slurp', 'facebookexternalhit',
        'linkedinbot', 'twitterbot', 'whatsapp', 'curl', 'wget',
        'python-requests', 'go-http-client', 'okhttp',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Solo GETs que devuelven HTML (ignoramos AJAX, API, assets)
        if (! $request->isMethod('GET')) {
            return $response;
        }

        $path = '/' . ltrim($request->path(), '/');

        // ¿Es un path que queremos rastrear?
        $track = in_array($path, self::TRACKED_PATHS, true)
            || str_starts_with($path, '/v/');

        if (! $track) {
            return $response;
        }

        // Filtrar bots por user-agent
        $ua = strtolower((string) $request->userAgent());
        foreach (self::BOT_SIGNATURES as $sig) {
            if (str_contains($ua, $sig)) {
                return $response;
            }
        }

        // Registrar de forma segura (sin romper la request si falla)
        try {
            $referer  = $request->headers->get('referer');
            $domain   = PageView::extractDomain($referer);
            $category = PageView::categorize($domain);

            PageView::create([
                'path'             => $path,
                'referer'          => $referer,
                'referer_domain'   => $domain,
                'referer_category' => $category,
                'ip_hash'          => hash('sha256', $request->ip() . config('app.key')),
                'user_agent'       => $request->userAgent(),
            ]);
        } catch (\Throwable) {
            // Silenciar cualquier error de DB para no afectar al visitante
        }

        return $response;
    }
}
