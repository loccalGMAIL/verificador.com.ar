<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlanFeature
{
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        if ($this->shouldBypass($request)) {
            return $next($request);
        }

        if (! $this->hasFeature($request, $feature)) {
            activity()->log('feature.blocked', null, [
                'feature' => $feature,
                'route' => $request->route()?->getName(),
            ]);

            return redirect()->route('dashboard.subscription')
                ->with('feature_blocked', $feature);
        }

        return $next($request);
    }

    private function shouldBypass(Request $request): bool
    {
        $user = $request->user();

        return $user && ($user->role === 'admin' || session()->has('impersonating_admin_id'));
    }

    private function hasFeature(Request $request, string $feature): bool
    {
        $user = $request->user();

        if (! $user) {
            return false;
        }

        $subscription = $user->store?->subscription;

        if (! $subscription) {
            return false;
        }

        return $subscription->hasFeature($feature);
    }
}
