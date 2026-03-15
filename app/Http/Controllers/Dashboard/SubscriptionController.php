<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function index(): View
    {
        $store  = auth()->user()->store;
        $sub    = $store->subscription;
        $plans  = Plan::where('active', true)->orderBy('sort_order')->get();

        return view('dashboard.subscription.index', compact('store', 'sub', 'plans'));
    }
}
