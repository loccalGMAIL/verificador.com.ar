<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::with(['store.subscription.plan'])
            ->latest()
            ->paginate(30);

        $stores = Store::orderBy('name')->get(['id', 'name']);

        return view('admin.users.index', compact('users', 'stores'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'role'  => ['required', 'in:admin,owner,employee'],
        ]);

        $user->update($data);

        return back()->with('success', "Usuario \"{$user->name}\" actualizado.");
    }

    public function reassign(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'store_id' => ['nullable', 'exists:stores,id'],
            'role'     => ['required', 'in:owner,employee'],
        ]);

        $user->update([
            'store_id' => $data['store_id'] ?: null,
            'role'     => $data['store_id'] ? $data['role'] : 'employee',
        ]);

        $storeName = $data['store_id']
            ? Store::find($data['store_id'])->name
            : 'ninguno';

        return back()->with('success', "Usuario \"{$user->name}\" reasignado a {$storeName}.");
    }

    public function suspend(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'No podés suspender tu propia cuenta.');
        }

        $user->update(['status' => 'suspended']);

        return back()->with('success', "Usuario \"{$user->name}\" suspendido.");
    }

    public function reactivate(User $user): RedirectResponse
    {
        $user->update(['status' => 'active']);

        return back()->with('success', "Usuario \"{$user->name}\" reactivado.");
    }
}
