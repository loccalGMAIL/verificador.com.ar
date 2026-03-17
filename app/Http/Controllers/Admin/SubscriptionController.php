<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
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
}
