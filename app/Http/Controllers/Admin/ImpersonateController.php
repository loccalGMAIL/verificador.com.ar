<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonateController extends Controller
{
    public function impersonate(Request $request, User $user): RedirectResponse
    {
        // No impersonar a otro admin
        if ($user->isAdmin()) {
            return back()->with('error', 'No podés impersonar a otro administrador.');
        }

        $request->session()->put('impersonating_admin_id', Auth::id());

        Auth::loginUsingId($user->id);

        return redirect()->route('dashboard.home')
            ->with('success', "Navegando como {$user->name}.");
    }

    public function leave(Request $request): RedirectResponse
    {
        $adminId = $request->session()->pull('impersonating_admin_id');

        if (! $adminId) {
            return redirect()->route('dashboard.home');
        }

        Auth::loginUsingId($adminId);

        return redirect()->route('admin.home')
            ->with('success', 'Volviste a tu sesión de administrador.');
    }
}
