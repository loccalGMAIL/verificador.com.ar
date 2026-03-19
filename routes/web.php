<?php

use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ScanViewController;
use App\Http\Controllers\Dashboard\HomeController as DashboardHome;
use App\Http\Controllers\Dashboard\ProductController;
use App\Http\Controllers\Dashboard\ProductImportController;
use App\Http\Controllers\Dashboard\BranchController;
use App\Http\Controllers\Dashboard\PriceListController;
use App\Http\Controllers\Dashboard\ImportProfileController;
use App\Http\Controllers\Dashboard\SettingsController;
use App\Http\Controllers\Dashboard\SubscriptionController as DashboardSubscription;
use App\Http\Controllers\Admin\HomeController as AdminHome;
use App\Http\Controllers\Admin\StoreController as AdminStoreController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\SubscriptionController as AdminSubscriptionController;
use App\Http\Controllers\Admin\PlanController as AdminPlanController;
use App\Http\Controllers\Admin\ImpersonateController;
use Illuminate\Support\Facades\Route;

// ============================================================
// PÚBLICO — Landing page
// ============================================================
Route::get('/', fn () => view('welcome'))->name('home');

// ============================================================
// PÚBLICO — Escáner QR (clientes de los comercios)
// ============================================================
Route::get('/v/{token}', ScanViewController::class)
    ->name('scan.index');

// ============================================================
// AUTH — Solo para invitados
// ============================================================
Route::middleware('guest')->group(function () {

    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);

    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);

    Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('auth.google');
    Route::get('/auth/google/callback', [GoogleController::class, 'callback']);
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// Salir de impersonación (disponible cuando el usuario es owner/employee impersonado)
Route::post('/impersonate/leave', [ImpersonateController::class, 'leave'])
    ->middleware('auth')
    ->name('impersonate.leave');

// ============================================================
// DASHBOARD — Comercio (owner / employee)
// ============================================================
Route::middleware(['auth', 'role:owner,employee', 'subscription'])
    ->prefix('dashboard')
    ->name('dashboard.')
    ->group(function () {

        Route::get('/', DashboardHome::class)->name('home');

        // --- Productos: import primero para evitar conflicto con {product} ---
        Route::get('/products/import',                           [ProductImportController::class, 'index'])->name('products.import.index');
        Route::post('/products/import',                          [ProductImportController::class, 'store'])->name('products.import.store');
        Route::get('/products/import/template',                  [ProductImportController::class, 'template'])->name('products.import.template');
        Route::get('/products/import/{import}',                  [ProductImportController::class, 'show'])->name('products.import.show');
        Route::post('/products/import/{import}/cancel',          [ProductImportController::class, 'cancel'])->name('products.import.cancel');
        Route::post('/products/import/{import}/process',         [ProductImportController::class, 'process'])->name('products.import.process');
        Route::get('/products/import/{import}/progress',         [ProductImportController::class, 'progress'])->name('products.import.progress');

        Route::resource('products', ProductController::class)
            ->except(['show'])
            ->parameters(['products' => 'product']);

        // --- Listas de precios ---
        Route::resource('price-lists', PriceListController::class)
            ->except(['show'])
            ->parameters(['price-lists' => 'priceList']);
        Route::post('/price-lists/{priceList}/prices',       [PriceListController::class, 'savePrices'])->name('price-lists.prices');
        Route::post('/price-lists/{priceList}/recalculate',  [PriceListController::class, 'recalculate'])->name('price-lists.recalculate');

        // --- Sucursales ---
        Route::resource('branches', BranchController::class)
            ->except(['show']);
        Route::get('/branches/{branch}/qr',           [BranchController::class, 'qr'])->name('branches.qr');
        Route::get('/branches/{branch}/qr/configure', [BranchController::class, 'qrConfigure'])->name('branches.qr.configure');

        // --- Subscripción ---
        Route::get('/subscription', [DashboardSubscription::class, 'index'])->name('subscription');

        // --- Configuración ---
        Route::get('/settings',  [SettingsController::class, 'show'])->name('settings');
        Route::put('/settings',  [SettingsController::class, 'update'])->name('settings.update');

        // --- Perfiles de importación (gestionados desde Settings) ---
        Route::post('/settings/import-profiles',                       [ImportProfileController::class, 'store'])->name('settings.import-profiles.store');
        Route::put('/settings/import-profiles/{importProfile}',        [ImportProfileController::class, 'update'])->name('settings.import-profiles.update');
        Route::delete('/settings/import-profiles/{importProfile}',     [ImportProfileController::class, 'destroy'])->name('settings.import-profiles.destroy');
    });

// ============================================================
// ADMIN — Solo administradores
// ============================================================
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/', AdminHome::class)->name('home');

        // --- Comercios ---
        Route::get('/stores',                [AdminStoreController::class, 'index'])->name('stores.index');
        Route::get('/stores/{store}',        [AdminStoreController::class, 'show'])->name('stores.show');
        Route::post('/stores/{store}/suspend',    [AdminStoreController::class, 'suspend'])->name('stores.suspend');
        Route::post('/stores/{store}/reactivate', [AdminStoreController::class, 'reactivate'])->name('stores.reactivate');

        // --- Usuarios ---
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');

        // --- Subscripciones ---
        Route::get('/subscriptions',                               [AdminSubscriptionController::class, 'index'])->name('subscriptions.index');
        Route::post('/subscriptions/{subscription}/change-plan',   [AdminSubscriptionController::class, 'changePlan'])->name('subscriptions.change-plan');
        Route::post('/subscriptions/{subscription}/suspend',       [AdminSubscriptionController::class, 'suspend'])->name('subscriptions.suspend');
        Route::post('/subscriptions/{subscription}/reactivate',    [AdminSubscriptionController::class, 'reactivate'])->name('subscriptions.reactivate');
        Route::post('/subscriptions/{subscription}/reset-trial',   [AdminSubscriptionController::class, 'resetTrial'])->name('subscriptions.reset-trial');

        // --- Planes ---
        Route::resource('plans', AdminPlanController::class)
            ->except(['show']);

        // --- Impersonación ---
        Route::post('/users/{user}/impersonate', [ImpersonateController::class, 'impersonate'])
            ->name('users.impersonate');
    });
