<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function show(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            if (Auth::user()->isSuspended()) {
                Auth::logout();
                $request->session()->invalidate();

                return back()
                    ->withInput($request->only('email'))
                    ->withErrors(['email' => 'Tu cuenta está suspendida. Contactá al administrador.']);
            }

            $request->session()->regenerate();

            return $this->redirectAfterLogin();
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'Las credenciales no son correctas.']);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    private function redirectAfterLogin(): RedirectResponse
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return redirect()->route('admin.home');
        }

        return redirect()->route('dashboard.home');
    }
}
