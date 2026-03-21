<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class InviteController extends Controller
{
    public function show(string $token): RedirectResponse
    {
        $store = Store::where('invite_token', $token)->first();

        if (! $store) {
            return redirect()->route('login')
                ->with('error', 'El enlace no es válido o fue regenerado.');
        }

        if (Auth::check()) {
            $user = Auth::user();

            if ($user->store_id && $user->store_id !== $store->id) {
                return redirect()->route('dashboard.users.index')
                    ->with('error', 'Tu cuenta ya pertenece a otro comercio.');
            }

            if ($user->store_id !== $store->id) {
                $user->update(['store_id' => $store->id, 'role' => 'employee']);
            }

            return redirect()->route('dashboard.users.index')
                ->with('success', 'Te uniste al comercio como empleado.');
        }

        session(['invite_token' => $token]);

        return redirect()->route('auth.google');
    }
}
