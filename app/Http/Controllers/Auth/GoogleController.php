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

        // Consumir token de invitación de sesión (si existe)
        $inviteToken = session()->pull('invite_token');
        $store = $inviteToken ? Store::where('invite_token', $inviteToken)->first() : null;

        if ($inviteToken && ! $store) {
            return redirect()->route('login')
                ->with('error', 'El enlace de invitación expiró. Pedí uno nuevo al dueño.');
        }

        // Buscar usuario existente por google_id o email
        $user = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        if ($user) {
            if ($store) {
                if ($user->store_id && $user->store_id !== $store->id) {
                    return redirect()->route('login')
                        ->with('error', 'Tu cuenta ya pertenece a otro comercio.');
                }
                if ($user->store_id !== $store->id) {
                    $user->update(['store_id' => $store->id, 'role' => 'employee']);
                }
            }
            // Actualizar google_id si no lo tenía
            if (! $user->google_id) {
                $user->update(['google_id' => $googleUser->getId()]);
            }
        } else {
            if ($store) {
                // Nuevo usuario invitado → solo User como employee, sin Store ni Subscription
                $user = User::create([
                    'name'      => $googleUser->getName(),
                    'email'     => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'password'  => null,
                    'role'      => 'employee',
                    'store_id'  => $store->id,
                ]);
            } else {
                // Primer acceso normal → crear comercio + usuario + trial
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
                        'trial_ends_at' => now()->addDays(config('app.trial_days')),
                    ]);
                });
            }
        }

        if ($user->isSuspended()) {
            return redirect()->route('login')
                ->with('error', 'Tu cuenta está suspendida. Contactá al administrador.');
        }

        Auth::login($user);

        return $user->isAdmin()
            ? redirect()->route('admin.home')
            : redirect()->route('dashboard.home');
    }
}
