# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**verificador.com.ar** — A SaaS price-verification platform for Argentine retail stores. Merchants place QR codes on shelves; customers scan them to see live product prices. Currently in early/landing-page stage.

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
`routes/web.php` → Controller or closure → Blade view

Currently the single route (`/`) returns the `welcome` view directly — no controller involved yet.

### Blade layout system
- `resources/views/layouts/public.blade.php` — main public layout; includes Tailwind CDN, Font Awesome CDN, `public/css/app.css`, nav, and footer. Uses `@yield('title')`, `@yield('content')`, `@stack('styles')`, `@stack('scripts')`.
- Views extend `layouts.public` and fill `@section('content')`.

### Database
- Uses MySQL in production/local (not SQLite).
- `phpunit.xml` has SQLite in-memory commented out — tests run against the configured DB unless overridden.
- Session, cache, and queue are all database-backed in the current `.env`.

### Frontend
- No Vite/npm build pipeline is active yet (`package.json` is essentially empty).
- All CSS utilities come from the Tailwind CDN `<script>` tag in the layout.
- Custom styles go in `public/css/app.css`.
- Static images live in `public/Images/`.
