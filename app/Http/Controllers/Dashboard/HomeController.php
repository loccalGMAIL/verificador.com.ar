<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $store    = auth()->user()->store;
        $branches = $store?->branches()->where('active', true)->orderBy('name')->get() ?? collect();

        return view('dashboard.home', compact('branches'));
    }
}
