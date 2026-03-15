<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    /** Redirige al usuario a la página de autenticación de Google */
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    /** Maneja el callback de Google */
    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (\Throwable $e) {
            return redirect()->route('login')
                ->with('error', 'No se pudo autenticar con Google. Intentá de nuevo.');
        }

        // Buscar usuario existente por google_id o email
        $user = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        if ($user) {
            // Actualizar google_id si no lo tenía
            if (! $user->google_id) {
                $user->update(['google_id' => $googleUser->getId()]);
            }
        } else {
            // Primer acceso con Google → crear comercio + usuario + trial
            DB::transaction(function () use ($googleUser, &$user) {
                $storeName = $googleUser->getName() . "'s Store";

                $store = Store::create([
                    'name'   => $storeName,
                    'slug'   => Str::slug($storeName) . '-' . Str::random(6),
                    'status' => 'active',
                ]);

                $user = User::create([
                    'name'      => $googleUser->getName(),
                    'email'     => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'password'  => null,
                    'role'      => 'owner',
                    'store_id'  => $store->id,
                ]);

                $basicPlan = Plan::where('name', 'Basic')->first();
                Subscription::create([
                    'store_id'      => $store->id,
                    'plan_id'       => $basicPlan->id,
                    'status'        => 'trial',
                    'trial_ends_at' => now()->addDays(7),
                ]);
            });
        }

        Auth::login($user);

        return $user->isAdmin()
            ? redirect()->route('admin.home')
            : redirect()->route('dashboard.home');
    }
}
