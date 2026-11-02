<?php

namespace App\Jobs;

use App\Models\OdooConnection;
use App\Services\Odoo\OdooException;
use App\Services\Odoo\OdooProductSyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncOdooProducts implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 600;

    public function __construct(
        public readonly OdooConnection $odooConnection
    ) {}

    public function handle(OdooProductSyncService $service): void
    {
        set_time_limit($this->timeout);

        if (! $this->odooConnection->enabled) {
            return;
        }

        try {
            $service->sync($this->odooConnection);
        } catch (OdooException) {
            // El error queda registrado en la conexión (last_error) y se muestra en Configuración
        }
    }
}
