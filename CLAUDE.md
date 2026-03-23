# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**verificador.com.ar** — A SaaS price-verification platform for Argentine retail stores. Merchants place QR codes on shelves; customers scan them to see live product prices. The platform is in active development with a working MVP including auth, subscription management, product/branch management, and a public scan interface.

- Laravel 11.x, PHP 8.2+
- MySQL database (`verificador`)
- Locale: `es` / timezone: `America/Argentina/Buenos_Aires`
- Tailwind CSS loaded via **CDN** (no npm/Vite build step for CSS)
- Font Awesome via CDN

## Commands

```bash
# Start full dev stack (server + queue + logs + vite)
composer run dev

# Start only the HTTP server
php artisan serve

# Run all tests
php artisan test
# or
./vendor/bin/phpunit

# Run a single test file
php artisan test tests/Feature/ExampleTest.php

# Run a specific test method
php artisan test --filter ExampleTest::test_the_application_returns_a_successful_response

# Code style (Laravel Pint)
./vendor/bin/pint

# Database migrations
php artisan migrate

# Tinker REPL
php artisan tinker
```

## Architecture

### Request flow
`routes/web.php` → Controller → Blade view

### Route groups
- `/` — public landing page (welcome view)
- `/v/{token}` — public QR scan interface (`ScanViewController`) — tracked by `LogPageView` middleware
- `/api/scan/{token}/{barcode}` — JSON API for barcode lookup (`Api\ScanController`) — logs to `product_searches`
- `/dashboard/*` — merchant dashboard (auth + role + subscription middleware)
- `/admin/*` — admin panel (auth + role:admin middleware)

### Blade layout system
- `resources/views/layouts/public.blade.php` — public layout (landing/scan pages)
- `resources/views/layouts/app.blade.php` — dashboard layout; uses `@yield('title')`, `@yield('page-title')`, `@yield('content')`, `@stack('styles')`, `@stack('scripts')`.
- Views extend `layouts.app` and fill `@section('content')`.

### Key models
- `Store` — root entity; owns branches, products, subscription, users
- `Branch` — store location with `qr_token` (UUID) used in scan URLs
- `Product` / `ProductPrice` — products with optional price lists
- `Subscription` / `Plan` — plan limits (`max_products`, `max_branches`, `max_price_lists`), statuses: trial, active, suspended, cancelled
- `PageView` — anonymous tracking of public page visits (`/`, `/v/{token}`, etc.)
- `ProductSearch` — logs each barcode lookup via the scan API (`branch_id`, `product_id`, `barcode`, `found`)
- `User` — with roles (owner, employee, admin); a store can have multiple users

### Anti-bot en registro
- `POST /register` tiene tres capas de protección contra bots:
  1. **Rate limiting** — 1 intento por minuto por IP (named limiter `register` en `AppServiceProvider`; middleware `throttle:register` en la ruta).
  2. **Honeypot** — campo `website` off-screen en el formulario; si viene completado, `RegisterController::store()` redirige silenciosamente sin error.
  3. **hCaptcha** — widget visible en el formulario; verificación server-side en `app/Rules/HCaptcha.php` contra `api.hcaptcha.com/siteverify`. Credenciales en `.env`: `HCAPTCHA_SITE_KEY` y `HCAPTCHA_SECRET`.
- El flujo Google OAuth (`GoogleController`) no pasa por estas capas (Google ya garantiza que es un humano).

### Analytics & tracking
- `LogPageView` middleware logs GET requests to `/` and `/v/*` paths into `page_views` (bot-filtered, no PII).
- `Api\ScanController` logs every barcode scan into `product_searches` (found/not found).
- Dashboard home (merchant) shows: plan usage, QR visits per branch, top 5 most searched products.
- Dashboard admin shows: store/subscription KPIs, scan activity, hit rate, trials por vencer, distribución por plan, top comercios activos, gráfico mixto visitas+búsquedas 14d.
- `/dashboard/estadisticas` (`StatisticsController`) — página de estadísticas avanzadas para el comerciante; selector de período 7d/30d/90d; muestra: gráfico mixto tendencias (visitas + búsquedas), tabla comparativa por sucursal con tasa de éxito, gráfico de horas pico (CONVERT_TZ a UTC-3), y top 20 barcodes no encontrados.

### Branding en vistas públicas
- `resources/views/scan/index.blade.php` — vista móvil del QR; tiene barra fija en el fondo que publicita verificador.com.ar.
- `resources/views/layouts/public.blade.php` — footer de la landing incluye crédito "Diseñado por pez.com.ar".

### Vista de escaneo (`/v/{token}`)
- Dos modos de búsqueda: **cámara** (acordeón superior, abierto por defecto) y **búsqueda manual** (acordeón inferior, colapsado por defecto).
- Los acordeones son mutuamente excluyentes: abrir uno colapsa el otro.
- La búsqueda manual acepta entrada numérica (`type="tel"`) y soporta Enter para confirmar.
- Ambos modos comparten la misma función `lookupBarcode()` y la misma área de resultados.
- El logging a `product_searches` ocurre igual para ambos modos (mismo endpoint API).

### Database
- Uses MySQL in production/local (not SQLite).
- `phpunit.xml` has SQLite in-memory commented out — tests run against the configured DB unless overridden.
- Session, cache, and queue are all database-backed in the current `.env`.

### Frontend
- No Vite/npm build pipeline is active yet (`package.json` is essentially empty).
- All CSS utilities come from the Tailwind CDN `<script>` tag in the layout.
- Custom styles go in `public/css/app.css`.
- Static images live in `public/Images/`.
