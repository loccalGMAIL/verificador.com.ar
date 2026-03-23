<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\PriceList;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\User;
use App\Rules\HCaptcha;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function show(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        // Honeypot: si el campo señuelo fue completado, es un bot
        if ($request->filled('website')) {
            return redirect()->route('register');
        }

        $data = $request->validate([
            'name'               => ['required', 'string', 'max:255'],
            'store_name'         => ['required', 'string', 'max:255'],
            'email'              => ['required', 'email', 'max:255', 'unique:users'],
            'password'           => ['required', 'string', 'min:8', 'confirmed'],
            'h-captcha-response' => ['required', new HCaptcha],
        ], [
            'h-captcha-response.required' => 'Por favor completá la verificación de seguridad.',
        ]);

        DB::transaction(function () use ($data) {
            // 1. Crear el comercio
            $store = Store::create([
                'name'   => $data['store_name'],
                'slug'   => Str::slug($data['store_name']) . '-' . Str::random(6),
                'status' => 'active',
            ]);

            // 2. Crear el usuario dueño
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
                'role'     => 'owner',
                'store_id' => $store->id,
            ]);

            // 3. Crear lista de precios General por defecto
            PriceList::create([
                'store_id'   => $store->id,
                'name'       => 'General',
                'description' => 'Lista de precios principal',
                'is_default' => true,
                'active'     => true,
                'sort_order' => 0,
            ]);

            // 4. Crear suscripción en trial por 7 días
            $basicPlan = Plan::where('name', 'Basic')->first();
            Subscription::create([
                'store_id'      => $store->id,
                'plan_id'       => $basicPlan->id,
                'status'        => 'trial',
                'trial_ends_at' => now()->addDays(config('app.trial_days')),
            ]);

            // 5. Crear sucursal principal automáticamente
            if ($store->branches()->count() === 0) {
                $store->branches()->create([
                    'name'      => $store->name,
                    'active'    => true,
                    'qr_token'  => \Illuminate\Support\Str::random(12),
                ]);
            }

            Auth::login($user);
        });

        return redirect()->route('dashboard.home');
    }
}
