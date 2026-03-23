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
- `resources/views/layouts/admin.blade.php` — admin layout; sidebar oscuro, mismo sistema de yields. El header muestra el nombre del admin como botón que abre el modal de cambio de contraseña propia.
- Views extend `layouts.app` and fill `@section('content')`.

### Key models
- `Store` — root entity; owns branches, products, subscription, users
- `Branch` — store location with `qr_token` (UUID) used in scan URLs
- `Product` / `ProductPrice` — products with optional price lists
- `Subscription` / `Plan` — plan limits (`max_products`, `max_branches`, `max_price_lists`), statuses: trial, active, suspended, cancelled
- `PageView` — anonymous tracking of public page visits (`/`, `/v/{token}`, etc.)
- `ProductSearch` — logs each barcode lookup via the scan API (`branch_id`, `product_id`, `barcode`, `found`)
- `User` — with roles (owner, employee, admin); a store can have multiple users

### Gestión de contraseñas en el panel admin
- **Admin cambia su propia contraseña** — modal accesible desde el nombre en el header de `layouts/admin.blade.php`. Requiere contraseña actual + nueva + confirmación. Si el admin usa Google OAuth (`password === null`), no se pide contraseña actual. El modal se reabre automáticamente si la validación falla (Alpine inicializado con `open: true` cuando `$errors` contiene claves de contraseña).
- **Admin resetea contraseña de usuarios** — botón de llave (indigo) por fila en `/admin/users`. Modal con nueva contraseña + confirmación, sin requerir contraseña actual. Funciona también para usuarios Google OAuth con `password = null`. Ruta: `PUT /admin/users/{user}/reset-password` → `Admin\UserController@resetUserPassword`.
- Rutas: `admin.profile.password` y `admin.users.reset-password`. Ambas en `Admin\UserController`.

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

### Integración MercadoPago (suscripciones recurrentes)

- **Flujo:** "Preaprobaciones sin plan asociado — pago pendiente". Se crea la preapproval con `status: pending` y `auto_recurring` inline → el usuario es redirigido a `init_point` en MP para ingresar su medio de pago → MP notifica vía webhook cuando el pago es autorizado.
- **Servicio:** `app/Services/MercadoPagoService.php` — métodos: `createPreapproval()`, `getPreapproval()`, `cancelPreapproval()` (usa PUT, no PATCH), `getAuthorizedPayment()`, `verifyWebhookSignature()`.
- **Config:** `config/mercadopago.php` — lee `MP_ACCESS_TOKEN`, `MP_WEBHOOK_SECRET`, construye `back_url` y `notification_url` desde `APP_URL`.
- **Variables de entorno requeridas:**
  - `MP_ACCESS_TOKEN` — token del vendedor (en sandbox: token del usuario de prueba vendedor `APP_USR-...`)
  - `MP_WEBHOOK_SECRET` — clave HMAC para verificar firma `x-signature`
  - `MP_TEST_PAYER_EMAIL` — solo en sandbox: email del usuario de prueba comprador (payer y collector no pueden ser el mismo usuario ni mezclarse reales/test)
- **Webhook:** `POST /webhooks/mercadopago` → `App\Http\Controllers\Webhook\MercadoPagoController` (excluido de CSRF en `bootstrap/app.php`). Maneja dos tipos:
  - `subscription_preapproval` — actualiza `status`, `starts_at`, `mp_payer_id`, `mp_payer_email` en `subscriptions`
  - `subscription_authorized_payment` — crea/actualiza registro en `subscription_payments` (idempotente por `mp_payment_id` único)
- **Modelos:** `Subscription` tiene `mp_subscription_id`, `mp_payer_id`, `mp_payer_email`, `starts_at`. Relación `payments()` → `SubscriptionPayment`. Modelo `SubscriptionPayment` registra cada cobro recurrente con `amount`, `currency`, `status` (processed/recycling/cancelled), `paid_at`.
- **Rutas nuevas:**
  - `POST /dashboard/subscription/subscribe/{plan}` → inicia flujo MP o activa plan gratuito directamente
  - `GET /dashboard/subscription/return` → retorno desde MP, sincroniza estado
  - `GET /admin/subscriptions/{subscription}` → detalle admin con historial de pagos
- **Verificación de firma HMAC:** `data.id` viene del query string (no del body). Template: `id:{dataId};request-id:{xRequestId};ts:{ts};`. Si `MP_WEBHOOK_SECRET` está vacío, se omite la verificación (solo en local/dev).
- **Sandbox:** `APP_URL` debe ser una URL pública (ngrok). MP rechaza localhost en `back_url`/`notification_url`. El `notification_url` se pasa en el payload de creación del preapproval (no se puede configurar a nivel de app para suscripciones).

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
