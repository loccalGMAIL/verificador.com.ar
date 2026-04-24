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
