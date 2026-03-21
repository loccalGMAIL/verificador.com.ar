<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StoreUserController extends Controller
{
    public function index(): View
    {
        $store = auth()->user()->store;
        $users = $store->users()->orderBy('role')->get();

        return view('dashboard.users.index', compact('store', 'users'));
    }

    public function generateInvite(): RedirectResponse
    {
        if (! auth()->user()->isOwner()) {
            abort(403);
        }

        $store = auth()->user()->store;
        $token = $store->generateInviteToken();
        $link  = route('invite.show', $token);

        return redirect()->route('dashboard.users.index')
            ->with('invite_link', $link);
    }

    public function removeEmployee(User $user): RedirectResponse
    {
        $authUser = auth()->user();

        if (! $authUser->isOwner()) {
            abort(403);
        }

        if ($user->store_id !== $authUser->store_id) {
            abort(403);
        }

        if ($user->isOwner()) {
            abort(403, 'No podés quitar al dueño.');
        }

        $user->update(['store_id' => null]);

        return redirect()->route('dashboard.users.index')
            ->with('success', 'El empleado fue removido del comercio.');
    }
}
