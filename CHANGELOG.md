# Changelog

Todos los cambios notables del proyecto se documentan en este archivo.

El formato sigue [Keep a Changelog](https://keepachangelog.com/es/1.1.0/),
y el proyecto adhiere a [Semantic Versioning](https://semver.org/lang/es/).

---

## [Unreleased]

---

## [1.15.0] - 2026-05-07

### Mejorado

- **Escáner QR** (`/v/{token}`) — serie de mejoras de rendimiento y UX en la vista de escaneo:
  - `qrbox` dinámico: se adapta al tamaño real del visor (85% del ancho, 35% del alto) en lugar de píxeles fijos.
  - FPS subido de 10 a 15 para detección más rápida.
  - Activado `BarcodeDetector` nativo en browsers compatibles (Chrome/Android) para mayor velocidad de lectura.
  - Indicador pulsante verde "Escaneando..." en la barra de cámara como feedback visual de estado activo.
  - Vibración de 150 ms al escanear exitosamente en Android.
  - Hint en amber para iPhone/iPad explicando la distancia mínima de enfoque (~15 cm).
  - UI interna de html5-qrcode ocultada para una interfaz más limpia.

---

## [1.14.0] - 2026-05-07

### Agregado

- **Campos personalizados de producto** — los comercios pueden definir atributos extra por tienda (`product_custom_field_definitions`): etiqueta visible, columna Excel para importación, visibilidad en el escáner y orden de aparición.
  - Los valores se almacenan como JSON en `products.custom_fields`, indexados por `excel_column`.
  - La importación por Excel/CSV soporta las columnas de campos personalizados; los valores se fusionan sin sobrescribir datos existentes.
  - El escáner (`/v/{token}`) muestra los campos marcados como visibles, filtrando los vacíos.
  - Preview de apariencia en `/dashboard/settings` refleja los campos visibles en tiempo real.

- **Vista matriz de campos personalizados** (`/dashboard/products/campos`) — tabla con productos como filas y campos personalizados como columnas; las celdas vacías se destacan en ámbar. Soporta búsqueda y paginación.

- **Precio secundario desde campo personalizado** — nueva modalidad para el precio mayorista: en lugar de un descuento porcentual fijo, el precio puede tomarse directamente del valor de un campo personalizado del producto.
  - Configuración en `/dashboard/settings` (tab "Importación y precios"): radio button para elegir entre "Descuento porcentual" o "Campo personalizado".
  - Si el producto no tiene valor en el campo seleccionado, el bloque mayorista no se muestra en el escáner.
  - La FK `wholesale_custom_field_id` usa `nullOnDelete`: si se elimina la definición de campo, el precio mayorista queda inactivo silenciosamente.

### Modificado

- **Apariencia del escáner** — preview en vivo con Vite refleja los campos personalizados visibles configurados por la tienda.

---

## [1.13.1] - 2026-05-07

### Agregado

- **Sidebar condicional** — ítems bloqueados (candado + tooltip) para features no incluidas en el plan actual: Listas de precios (Pro), Sucursales (Business), Estadísticas avanzadas (Pro). El ítem "Listas de precios" fue descomentado y ahora se muestra condicionalmente.
- **Widget de capabilities** en `/dashboard/subscription` — grilla de features habilitadas/deshabilitadas para el plan actual, con indicador de upgrade si corresponde.
- **Instrumentación de activity log** — 13 nuevos tipos de eventos en controllers admin y dashboard:
  - Admin: `store.suspended`, `store.reactivated`, `store.deleted`
  - Admin: `subscription.plan_changed` (incluye old/new plan), `subscription.suspended`, `subscription.reactivated`, `subscription.trial_reset`
  - Admin: `user.suspended`, `user.reactivated`, `user.reassigned`, `user.password_reset`
  - Dashboard: `product.created`, `product.deleted`, `branch.created`, `branch.deleted`, `branch.qr_updated`, `price_list.created`, `price_list.deleted`, `import.started`, `import.cancelled`
- **Tests** — 17 nuevos casos de prueba:
  - `PlanHasFeatureTest` (6 casos unitarios, incluyendo validación del fix de ltrim)
  - `SubscriptionHasFeatureTest` (6 casos: trial, active, suspended, cancelled, expired trial)
  - `EnsurePlanFeatureTest` (5 casos feature: trial bypass, con/sin feature, impersonación, activity log)

### Corregido

- **`Plan::hasFeature()`** — reemplaza `ltrim($feature, 'has_')` (máscara de caracteres) por `str_starts_with()`, que corrompía `has_advanced_stats` → `dvanced_stats` y `has_api` → `pi`.

### Infraestructura

- Tests migrados de SQLite in-memory a MySQL (`verificador_testing`) para alinear con el entorno de producción y evitar incompatibilidades de sintaxis SQL.

---

## [1.13.0] - 2026-05-01

### Agregado

- **Registro de eventos (Activity Log)** en BD (`activity_log`)
  - Nueva tabla polimórfica que registra eventos críticos: auth (login/logout), creación de comercios, cambios de suscripción, impersonación, etc.
  - `ActivityLogger` service con API fluida para loggear eventos desde cualquier controller.
  - Vista admin global en `/admin/activity` con filtros por comercio, tipo de evento, fecha y usuario.

- **Capabilities por plan** — modelo de permisos booleanos en `plans`
  - 8 nuevas columnas: `has_import_history`, `has_basic_stats`, `has_advanced_stats`, `has_price_lists`, `has_customization`, `has_manual_search`, `has_branches`, `has_api`.
  - Mapeo actualizado en `PlansSeeder`: Basic (2K productos), Standard (5K + stats), Pro (10K + listas + customización), Business (ilimitado + API).
  - `Subscription::hasFeature()` — chequea si el plan tiene la capacidad (trial bypassea todo).
  - Middleware `EnsurePlanFeature` — protege rutas; redirige a `/dashboard/subscription` si falta el feature.

- **Enforcement de capabilities en rutas**
  - Listas de precios: `feature:has_price_lists`
  - Estadísticas avanzadas: `feature:has_advanced_stats`
  - Sucursales (CRUD): `feature:has_branches`
  - Historial de importaciones: `feature:has_import_history`
  - Configuración de apariencia/QR: `feature:has_customization`

- **Instrumentación de events** en controllers clave
  - `auth.login` — login por form o Google (captura provider)
  - `auth.logout` — logout
  - `store.created` — nueva tienda en register/Google
  - `auth.impersonation.start/stop` — impersonación de usuarios

### Modificado

- **Plans** (`app/Models/Plan.php`)
  - Método `hasFeature(string $feature): bool`
  - Fillable y casts con 8 nuevas columnas booleanas

- **Subscriptions** (`app/Models/Subscription.php`)
  - Método `hasFeature()` que valida trial, status activo y delega al plan

---

## [1.12.0] - 2026-04-24

### Agregado

- **Etiquetas** (`/dashboard/etiquetas`)
  - Nuevo control `Copias por producto` para repetir etiquetas en la vista previa A4 y en la impresion.

### Modificado

- **Etiquetas** (`/dashboard/etiquetas`)
  - Vista previa simplificada (solo texto, sin codigos de barras) para mejorar rendimiento; los codigos de barras quedan solo en la impresion.

---

## [1.2.0] — 2026-03-16

### Agregado

- **Listas de precios**
  - Nuevo modelo `PriceList`: cada comercio puede tener múltiples listas (General, Mayorista, VIP, etc.)
  - Nuevo modelo `ProductPrice`: precio de un producto en una lista específica
  - CRUD completo en `/dashboard/price-lists` (crear, editar, activar/desactivar, eliminar)
  - Al registrar un comercio se crea automáticamente la lista "General" como lista por defecto
  - Enlace "Listas de precios" en el sidebar del dashboard
  - Límite de listas por plan (`max_price_lists`): Basic/Standard = 1, Pro = 2, Business = ilimitado
  - Control de acceso: la lista por defecto no puede eliminarse ni desactivarse

- **Importación de productos — flujo de mapeo de columnas**
  - Paso 1: subida del archivo (xlsx/csv)
  - Paso 2: pantalla de mapeo (`/products/import/{id}/mapping`) con auto-detección de encabezados
  - Selección de lista de precios destino al importar
  - El job `ProcessProductImport` usa el mapeo guardado en lugar de nombres fijos de columna
  - Productos importados sin precio ya no se descartan (se crean sin precio en la lista)

- **Planes — campo `max_price_lists`**
  - Nueva migración `add_max_price_lists_to_plans_table`
  - Seeder actualizado con límites por plan
  - Helpers `hasPriceListLimit()` y `maxPriceListsLabel()` en el modelo `Plan`

- **Base de datos**
  - Migración `create_price_lists_table`
  - Migración `create_product_prices_table`
  - Migración `migrate_existing_product_prices` — migra precios legacy a la lista por defecto
  - Migración `add_mapping_to_product_imports_table` — columnas `mapping` (JSON) y `price_list_id`

### Modificado

- **Escáner QR** (`/v/{token}`)
  - La API `/api/scan/{token}/{barcode}` ahora devuelve los precios del producto en **todas las listas activas** del comercio
  - La vista del escáner muestra cada lista con su precio o indica "No disponible"
  - UI renovada: nombre del comercio, tabla de precios por lista, botón "Escanear otro" destacado

- **Productos — formularios crear/editar**
  - Los campos de precio se agrupan por lista de precios activa
  - Se elimina la validación que exigía al menos un precio (ahora es opcional)
  - Sincronización de campos legacy (`price_ars`, `price_usd`) con el primer precio guardado

- **`Store`** — nueva relación `priceLists()` ordenada por `sort_order`
- **`Product`** — nueva relación `prices()` y helper `priceForList(PriceList $list)`
- **`ProductImport`** — nuevos campos `mapping` y `price_list_id`; relación `priceList()`

---

## [1.1.0] — 2026-03-15

### Agregado
- **Impresión QR configurable**
  - Nueva página de configuración antes de imprimir (`/dashboard/branches/{id}/qr/configure`)
  - Vista previa en tiempo real via iframe (misma página de impresión escalada al 60%)
  - Personalización: 5 esquemas de color (azul, verde, oscuro, violeta, naranja)
  - Personalización: título principal e instrucción editables
  - Personalización: toggles para mostrar/ocultar logo y nombre de sucursal
  - Hoja de impresión rediseñada: A5 apaisado con 2 tarjetas idénticas por hoja y línea de corte

### Modificado
- **Dashboard home** reorganizado: stats + acciones rápidas en la misma fila; sucursales y QR debajo
- Acciones rápidas actualizadas: Importar CSV y Nueva sucursal
- Botón "Imprimir QR" en sucursales ahora abre la página de configuración
- Botón "Imprimir QR" agregado directamente en el home para cada sucursal activa

### Corregido
- `print-color-adjust: exact` para forzar impresión de fondos y gradientes en todos los navegadores
- Cache-busting en iframe del preview para reflejar cambios de esquema de color en tiempo real

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

[Unreleased]: https://github.com/loccalGMAIL/verificador.com.ar/compare/v1.14.0...HEAD
[1.14.0]: https://github.com/loccalGMAIL/verificador.com.ar/compare/v1.13.1...v1.14.0
[1.13.1]: https://github.com/loccalGMAIL/verificador.com.ar/compare/v1.13.0...v1.13.1
[1.13.0]: https://github.com/loccalGMAIL/verificador.com.ar/compare/v1.12.0...v1.13.0
[1.12.0]: https://github.com/loccalGMAIL/verificador.com.ar/compare/v1.2.0...v1.12.0
[1.2.0]: https://github.com/loccalGMAIL/verificador.com.ar/compare/v1.1.0...v1.2.0
[1.1.0]: https://github.com/loccalGMAIL/verificador.com.ar/compare/v0.2.0...v1.1.0
[0.2.0]: https://github.com/loccalGMAIL/verificador.com.ar/compare/v0.1.0...v0.2.0
[0.1.0]: https://github.com/loccalGMAIL/verificador.com.ar/releases/tag/v0.1.0
