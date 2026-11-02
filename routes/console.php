<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Red de seguridad: si algún webhook de MP no llega, los pagos se recuperan igual
Schedule::command('mp:sync-payments')->everySixHours()->withoutOverlapping();

// Integración Odoo: trae los cambios de catálogo de los comercios conectados
Schedule::command('odoo:sync')->everyThirtyMinutes()->withoutOverlapping();
