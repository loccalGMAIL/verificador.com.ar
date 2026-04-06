<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Services\MercadoPagoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function __construct(private MercadoPagoService $mp) {}

    public function index(): View
    {
        $store  = auth()->user()->store;
        $sub    = $store->subscription;
        $plans  = Plan::where('active', true)->orderBy('sort_order')->get();

        if ($sub) {
            $sub->load(['payments' => fn ($q) => $q->latest('paid_at')]);
        }

        return view('dashboard.subscription.index', compact('store', 'sub', 'plans'));
    }

    public function subscribe(Plan $plan): RedirectResponse
    {
        abort_if(! $plan->active, 403, 'Plan no disponible.');

        $user  = auth()->user();
        $store = $user->store;
        $sub   = $store->subscription;

        // Plan gratuito: suscribir directamente sin MP
        if (! $plan->isPaid()) {
            $sub->update(['plan_id' => $plan->id, 'status' => 'active']);

            return redirect()->route('dashboard.subscription')
                ->with('mp_return_status', 'success');
        }

        // Sin access token configurado → no se puede procesar el pago
        if (empty(config('mercadopago.access_token'))) {
            abort(503, 'El procesamiento de pagos no está disponible en este momento. Contactá al soporte.');
        }

        // Cancelar suscripción MP anterior si existe
        if ($sub && $sub->mp_subscription_id) {
            try {
                $this->mp->cancelPreapproval($sub->mp_subscription_id);
            } catch (\Exception $e) {
                Log::warning('No se pudo cancelar preapproval anterior al suscribir', [
                    'mp_subscription_id' => $sub->mp_subscription_id,
                    'error'              => $e->getMessage(),
                ]);
            }
        }

        // Crear nueva preaprobación en MP
        try {
            // En sandbox, MP_TEST_PAYER_EMAIL permite usar un email de usuario de prueba.
        // En producción esta variable no existe y se usa el email real del merchant.
        $payerEmail = config('mercadopago.test_payer_email') ?: $user->email;

        $result = $this->mp->createPreapproval($plan, $store, $payerEmail);
        } catch (\Exception $e) {
            Log::error('MP createPreapproval falló', ['store' => $store->id, 'error' => $e->getMessage()]);

            return redirect()->route('dashboard.subscription')
                ->with('mp_return_status', 'failure');
        }

        // Guardar el ID de MP en la suscripción local (status no cambia hasta el webhook)
        $sub->update([
            'plan_id'            => $plan->id,
            'mp_subscription_id' => $result['id'],
        ]);

        return redirect()->away($result['init_point']);
    }

    public function billing(): View
    {
        $store = auth()->user()->store;
        $sub   = $store->subscription;

        if ($sub) {
            $sub->load(['plan', 'payments' => fn ($q) => $q->latest('paid_at')]);
        }

        // Calcular próximo vencimiento
        $nextDue = null;
        if ($sub) {
            if ($sub->isOnTrial()) {
                $nextDue = $sub->trial_ends_at;
            } elseif ($sub->isActive()) {
                $lastPayment = $sub->payments->where('status', 'processed')->first();
                if ($lastPayment?->paid_at) {
                    $nextDue = $lastPayment->paid_at->addMonth();
                } elseif ($sub->starts_at) {
                    $nextDue = $sub->starts_at->addMonth();
                }
            }
        }

        return view('dashboard.billing.index', compact('sub', 'nextDue'));
    }

    public function returnFromMp(Request $request): RedirectResponse
    {
        $preapprovalId = $request->query('preapproval_id');
        $mpStatus      = null;

        if ($preapprovalId) {
            try {
                $mpData   = $this->mp->getPreapproval($preapprovalId);
                $mpStatus = $mpData['status'] ?? null;

                $sub = auth()->user()->store->subscription;

                if ($sub && $sub->mp_subscription_id === $preapprovalId) {
                    $localStatus = match ($mpStatus) {
                        'authorized' => 'active',
                        'paused'     => 'suspended',
                        'cancelled'  => 'cancelled',
                        default      => null,
                    };

                    if ($localStatus && $sub->status !== $localStatus) {
                        $updates = ['status' => $localStatus];
                        if ($localStatus === 'active' && ! $sub->starts_at) {
                            $updates['starts_at'] = now();
                        }
                        if (! empty($mpData['payer_id']) && ! $sub->mp_payer_id) {
                            $updates['mp_payer_id'] = $mpData['payer_id'];
                        }
                        $sub->update($updates);
                    }
                }
            } catch (\Exception $e) {
                Log::warning('No se pudo verificar preapproval en el retorno de MP', [
                    'preapproval_id' => $preapprovalId,
                    'error'          => $e->getMessage(),
                ]);
            }
        }

        // Determinar el flash según el status de MP
        $flashStatus = match ($mpStatus) {
            'authorized' => 'success',
            'pending'    => 'pending',
            default      => ($request->query('status') === 'approved' ? 'success' : 'pending'),
        };

        return redirect()->route('dashboard.subscription')
            ->with('mp_return_status', $flashStatus);
    }
}
