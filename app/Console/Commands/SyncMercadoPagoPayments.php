<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Services\SubscriptionPaymentSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncMercadoPagoPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mp:sync-payments
                            {--subscription= : ID de suscripción local para sincronizar solo una}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza estado y pagos de las suscripciones contra la API de MercadoPago (red de seguridad si los webhooks fallan)';

    public function handle(SubscriptionPaymentSyncService $sync): int
    {
        if (empty(config('mercadopago.access_token'))) {
            $this->error('MP_ACCESS_TOKEN no está configurado.');

            return self::FAILURE;
        }

        $query = Subscription::query()->whereNotNull('mp_subscription_id');

        if ($subscriptionId = $this->option('subscription')) {
            $query->whereKey($subscriptionId);
        } else {
            $query->where('status', '!=', 'cancelled');
        }

        $failures = 0;

        foreach ($query->get() as $subscription) {
            try {
                $count = $sync->syncSubscription($subscription);

                $this->info("Suscripción #{$subscription->id} ({$subscription->mp_subscription_id}): {$count} pago(s) sincronizado(s).");
            } catch (\Exception $e) {
                $failures++;

                $this->error("Suscripción #{$subscription->id}: {$e->getMessage()}");

                Log::error('mp:sync-payments falló para una suscripción', [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $failures === 0 ? self::SUCCESS : self::FAILURE;
    }
}
