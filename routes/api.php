<?php

use App\Http\Controllers\Api\ScanController;
use Illuminate\Support\Facades\Route;

// Consulta pública de precio por código de barras
// GET /api/scan/{branch_token}/{barcode}
Route::get('/scan/{token}/{barcode}', ScanController::class)
    ->name('api.scan');
