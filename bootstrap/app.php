<?php

use App\Http\Middleware\CheckSubscription;
use App\Http\Middleware\EnsurePlanFeature;
use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\LogPageView;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->validateCsrfTokens(except: [
            'webhooks/mercadopago',
        ]);

        $middleware->alias([
            'role' => EnsureRole::class,
            'subscription' => CheckSubscription::class,
            'feature' => EnsurePlanFeature::class,
        ]);

        // Registrar visitas a las páginas públicas (landing, login, register, QR scanner)
        $middleware->web(append: [
            LogPageView::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
