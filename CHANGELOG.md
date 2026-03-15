# Changelog

Todos los cambios notables del proyecto se documentan en este archivo.

El formato sigue [Keep a Changelog](https://keepachangelog.com/es/1.1.0/),
y el proyecto adhiere a [Semantic Versioning](https://semver.org/lang/es/).

---

## [Unreleased]

---

## [0.2.0] — 2026-03-11

### Agregado
- **Autenticación**
  - Registro de usuarios con email/contraseña
  - Login y logout
  - Login con Google OAuth (Socialite)
  - Middleware `role` para control de acceso por rol (`admin`, `owner`, `employee`)
  - Contraseña nullable en usuarios (para OAuth)

- **Dashboard (comercios)**
  - Vista principal del dashboard (`/dashboard`)
  - CRUD completo de productos (`/dashboard/products`)
  - Importación masiva de productos vía CSV (`/dashboard/products/import`) con plantilla descargable
  - CRUD de sucursales (`/dashboard/branches`) con generación y vista de impresión de código QR
  - Página de configuración del comercio (`/dashboard/settings`)
  - Página "Próximamente" para secciones en desarrollo

- **Panel de administración**
  - Vista general del admin (`/admin`)
  - Listado de comercios con acciones de suspender/reactivar
  - Listado de usuarios
  - Gestión de suscripciones: cambio de plan, suspender, reactivar, resetear trial
  - CRUD de planes

- **Escáner QR (clientes)**
  - Ruta pública `/v/{token}` para que los clientes consulten precios escaneando un QR

- **API**
  - Estructura inicial de controladores API (`app/Http/Controllers/Api/`)
  - Archivo `routes/api.php`

- **Base de datos**
  - Migraciones: `stores`, `branches`, `products`, `product_imports`, `subscriptions`, `plans`, `page_views`
  - Campos adicionales en `users`: rol, store_id, etc.
  - Seeder de planes por defecto (`PlansSeeder`)

- **Modelos**
  - `Store`, `Branch`, `Product`, `ProductImport`, `Subscription`, `Plan`, `PageView`
  - `User` actualizado con relaciones y campo de rol

- **Layouts**
  - `layouts/app.blade.php` — layout para el dashboard de comercios
  - `layouts/admin.blade.php` — layout para el panel de administración

- **Infraestructura**
  - Dependencia `laravel/socialite` para OAuth
  - Jobs base (`app/Jobs/`)

### Modificado
- `layouts/public.blade.php` — ajustes de navegación y estilos
- `welcome.blade.php` — mejoras en la landing page
- `config/services.php` — credenciales de Google OAuth
- `bootstrap/app.php` — registro del middleware `role`

---

## [0.1.0] — 2026-03-10

### Agregado
- Proyecto Laravel 11 inicial
- Landing page pública (`/`) con diseño Tailwind CDN
- Layout público (`layouts/public.blade.php`) con nav y footer
- Animaciones y navegación de la landing
- Favicon personalizado
- CSS base en `public/css/app.css`

---

[Unreleased]: https://github.com/tu-usuario/verificador.com.ar/compare/v0.2.0...HEAD
[0.2.0]: https://github.com/tu-usuario/verificador.com.ar/compare/v0.1.0...v0.2.0
[0.1.0]: https://github.com/tu-usuario/verificador.com.ar/releases/tag/v0.1.0
