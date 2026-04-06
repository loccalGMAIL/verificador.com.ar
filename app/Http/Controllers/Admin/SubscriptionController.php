<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
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
        $subscriptions = Subscription::with(['store', 'plan'])
            ->latest()
            ->paginate(30);

        return view('admin.subscriptions.index', compact('subscriptions'));
    }

    public function changePlan(Request $request, Subscription $subscription): RedirectResponse
    {
        $request->validate([
            'plan_id' => ['required', 'exists:plans,id'],
        ]);

        $subscription->update([
            'plan_id' => $request->plan_id,
            'status'  => 'active',
        ]);

        return back()->with('success', 'Plan actualizado correctamente.');
    }

    public function suspend(Subscription $subscription): RedirectResponse
    {
        if ($subscription->mp_subscription_id) {
            try {
                $this->mp->cancelPreapproval($subscription->mp_subscription_id);
            } catch (\Exception $e) {
                Log::error('MP cancelPreapproval falló al suspender', [
                    'subscription' => $subscription->id,
                    'error'        => $e->getMessage(),
                ]);
            }
        }

        $subscription->update(['status' => 'suspended']);

        return back()->with('success', 'Subscripción suspendida.');
    }

    public function reactivate(Subscription $subscription): RedirectResponse
    {
        $subscription->update(['status' => 'active']);

        return back()->with('success', 'Subscripción reactivada.');
    }

    public function resetTrial(Subscription $subscription): RedirectResponse
    {
        $subscription->update([
            'status'        => 'trial',
            'trial_ends_at' => now()->addDays(config('app.trial_days')),
        ]);

        $days = config('app.trial_days');
        return back()->with('success', "Período trial reiniciado por {$days} días.");
    }

    public function show(Subscription $subscription): View
    {
        $subscription->load(['store', 'plan', 'payments' => fn ($q) => $q->latest('paid_at')]);

        return view('admin.subscriptions.show', compact('subscription'));
    }

    public function storePayment(Request $request, Subscription $subscription): RedirectResponse
    {
        $request->validate([
            'amount'   => ['required', 'numeric', 'min:0.01'],
            'currency' => ['required', 'string', 'size:3'],
            'paid_at'  => ['required', 'date'],
            'notes'    => ['nullable', 'string', 'max:500'],
        ]);

        SubscriptionPayment::create([
            'subscription_id' => $subscription->id,
            'mp_payment_id'   => null,
            'amount'          => $request->amount,
            'currency'        => strtoupper($request->currency),
            'status'          => 'processed',
            'paid_at'         => $request->paid_at,
            'notes'           => $request->notes,
        ]);

        if ($subscription->status !== 'active') {
            $subscription->update(['status' => 'active']);
        }

        return back()->with('success', 'Pago registrado correctamente.');
    }
}
