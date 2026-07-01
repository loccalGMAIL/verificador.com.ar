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

        $popupImageDataUri = null;
        $popupDuration = 6;
        if ($store?->scan_popup_enabled && $store->scan_popup_image_path
            && Storage::disk('public')->exists($store->scan_popup_image_path)) {
            $ext = strtolower(pathinfo($store->scan_popup_image_path, PATHINFO_EXTENSION));
            $mime = match ($ext) {
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                default => 'image/jpeg',
            };
            $popupImageDataUri = 'data:'.$mime.';base64,'.base64_encode(
                Storage::disk('public')->get($store->scan_popup_image_path)
            );
            $popupDuration = $store->scan_popup_duration ?? 6;
        }

        return view('scan.index', compact('token', 'store', 'branch', 'logoDataUri', 'serviceAvailable', 'popupImageDataUri', 'popupDuration'));
    }
}
