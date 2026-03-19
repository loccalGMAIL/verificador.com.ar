<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\View\View;

class ScanViewController extends Controller
{
    public function __invoke(string $token): View
    {
        $branch = Branch::where('qr_token', $token)->with('store')->first();
        $store  = $branch?->store;

        return view('scan.index', compact('token', 'store'));
    }
}
