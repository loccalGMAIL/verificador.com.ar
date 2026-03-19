<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    /**
     * Rutas del dashboard que se permiten incluso con suscripción expirada.
     * El usuario siempre debe poder ver y elegir su plan.
     */
    private const ALLOWED_ROUTES = [
        'dashboard.subscription',
        'logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Admins (o admin impersonando) no tienen restricción de suscripción
        if ($user->role === 'admin' || $request->session()->has('impersonating_admin_id')) {
            return $next($request);
        }

        // Permitir rutas exentas
        if ($request->routeIs(...self::ALLOWED_ROUTES)) {
            return $next($request);
        }

        $store = $user->store;
        $sub   = $store?->subscription;

        // Sin suscripción (no debería ocurrir, pero por las dudas)
        if (! $sub) {
            return redirect()->route('dashboard.subscription')
                ->with('subscription_expired', true);
        }

        // Suscripción expirada → bloqueo total
        if ($sub->isExpired()) {
            return redirect()->route('dashboard.subscription')
                ->with('subscription_expired', true);
        }

        return $next($request);
    }
}
