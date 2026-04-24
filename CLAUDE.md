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

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.3
- laravel/framework (LARAVEL) - v13
- laravel/prompts (PROMPTS) - v0
- laravel/socialite (SOCIALITE) - v5
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- phpunit/phpunit (PHPUNIT) - v12

## Skills Activation

This project has domain-specific skills available. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

- `laravel-best-practices` — Apply this skill whenever writing, reviewing, or refactoring Laravel PHP code. This includes creating or modifying controllers, models, migrations, form requests, policies, jobs, scheduled commands, service classes, and Eloquent queries. Triggers for N+1 and query performance issues, caching strategies, authorization and security patterns, validation, error handling, queue and job configuration, route definitions, and architectural decisions. Also use for Laravel code reviews and refactoring existing Laravel code to follow best practices. Covers any task involving Laravel backend PHP code patterns.
- `socialite-development` — Manages OAuth social authentication with Laravel Socialite. Activate when adding social login providers; configuring OAuth redirect/callback flows; retrieving authenticated user details; customizing scopes or parameters; setting up community providers; testing with Socialite fakes; or when the user mentions social login, OAuth, Socialite, or third-party authentication.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.
- To check environment variables, read the `.env` file directly.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This application uses PHPUnit for testing. All tests must be written as PHPUnit classes. Use `php artisan make:test --phpunit {name}` to create a new test.
- If you see a test using "Pest", convert it to PHPUnit.
- Every time a test has been updated, run that singular test.
- When the tests relating to your feature are passing, ask the user if they would like to also run the entire test suite to make sure everything is still passing.
- Tests should cover all happy paths, failure paths, and edge cases.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files; these are core to the application.

## Running Tests

- Run the minimal number of tests, using an appropriate filter, before finalizing.
- To run all tests: `php artisan test --compact`.
- To run all tests in a file: `php artisan test --compact tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --compact --filter=testName` (recommended after making a change to a related file).

</laravel-boost-guidelines>
