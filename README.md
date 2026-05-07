# verificador.com.ar

SaaS de verificacion de precios para comercios (AR): el comercio coloca un QR en gondola y el cliente consulta el precio (y listas activas) escaneando desde el celular.

## Stack

- Laravel 13 + PHP 8.3
- MySQL (por defecto: `verificador`)
- Tailwind via CDN (sin build de CSS)

Version: `config('app.version')`.

## Instalacion (local)

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
composer run dev
```

## Etiquetas (dashboard)

- Ruta: `/dashboard/etiquetas`
- `Copias por producto`: repite la etiqueta de cada producto tanto en la hoja A4 (vista previa) como en la impresion.
- Vista previa: se muestra en modo texto (sin codigos de barras) para mantener buen rendimiento; los codigos de barras se generan solo en la vista de impresion.

## Campos personalizados de producto

- Configuracion: `/dashboard/settings` → pestaña "Campos personalizados"
- Cada campo tiene una etiqueta visible para el cliente, un nombre de columna para la importacion Excel y un toggle de visibilidad en el escaneo.
- Los valores se importan via Excel/CSV y se almacenan como JSON en el producto.
- Vista matriz: `/dashboard/products/campos` — todos los productos vs. todos los campos.
- En el escaneo (`/v/{token}`) se muestran los campos marcados como visibles que tengan valor.

## Precio secundario (mayorista)

- Activar en `/dashboard/settings` → pestaña "Importacion y precios" → checkbox "Mostrar precio secundario".
- **Modalidad porcentual**: descuento fijo sobre el precio principal.
- **Modalidad campo personalizado**: toma el precio directamente del valor de un campo personalizado del producto; si el producto no tiene valor, el bloque no se muestra.
