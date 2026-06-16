<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ScanViewController extends Controller
{
    public function __invoke(string $token): View
    {
        $branch = Branch::where('qr_token', $token)->with('store')->first();
        $store = $branch?->store;

        $serviceAvailable = $branch && $store && $store->hasActiveSubscription();

        $logoDataUri = null;
        if ($store?->logo_path && Storage::disk('public')->exists($store->logo_path)) {
            $ext = strtolower(pathinfo($store->logo_path, PATHINFO_EXTENSION));
            $mime = match ($ext) {
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                default => 'image/jpeg',
            };
            $logoDataUri = 'data:'.$mime.';base64,'.base64_encode(
                Storage::disk('public')->get($store->logo_path)
            );
        }

        $promoDataUri = null;
        $promoShowWhen = null;

        if ($serviceAvailable
            && $branch?->promo_image_path
            && $branch->promo_show_when
            && Storage::disk('public')->exists($branch->promo_image_path)) {

            $ext = strtolower(pathinfo($branch->promo_image_path, PATHINFO_EXTENSION));
            $mime = match ($ext) {
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                default => 'image/jpeg',
            };
            $promoDataUri = 'data:'.$mime.';base64,'.base64_encode(
                Storage::disk('public')->get($branch->promo_image_path)
            );
            $promoShowWhen = $branch->promo_show_when;
        }

        return view('scan.index', compact(
            'token', 'store', 'branch', 'logoDataUri', 'serviceAvailable',
            'promoDataUri', 'promoShowWhen'
        ));
    }
}
