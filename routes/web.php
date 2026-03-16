<?php

use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Dashboard\HomeController as DashboardHome;
use App\Http\Controllers\Dashboard\ProductController;
use App\Http\Controllers\Dashboard\ProductImportController;
use App\Http\Controllers\Dashboard\BranchController;
use App\Http\Controllers\Dashboard\PriceListController;
use App\Http\Controllers\Dashboard\SettingsController;
use App\Http\Controllers\Dashboard\SubscriptionController as DashboardSubscription;
use App\Http\Controllers\Admin\HomeController as AdminHome;
use App\Http\Controllers\Admin\StoreController as AdminStoreController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\SubscriptionController as AdminSubscriptionController;
use App\Http\Controllers\Admin\PlanController as AdminPlanController;
use Illuminate\Support\Facades\Route;

// ============================================================
// PÚBLICO — Landing page
// ============================================================
Route::get('/', fn () => view('welcome'))->name('home');

// ============================================================
// PÚBLICO — Escáner QR (clientes de los comercios)
// ============================================================
Route::get('/v/{token}', fn ($token) => view('scan.index', compact('token')))
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

// ============================================================
// DASHBOARD — Comercio (owner / employee)
// ============================================================
Route::middleware(['auth', 'role:owner,employee', 'subscription'])
    ->prefix('dashboard')
    ->name('dashboard.')
    ->group(function () {

        Route::get('/', DashboardHome::class)->name('home');

        // --- Productos: import primero para evitar conflicto con {product} ---
        Route::get('/products/import',                          [ProductImportController::class, 'index'])->name('products.import.index');
        Route::post('/products/import',                         [ProductImportController::class, 'store'])->name('products.import.store');
        Route::get('/products/import/template',                 [ProductImportController::class, 'template'])->name('products.import.template');
        Route::get('/products/import/{import}/mapping',         [ProductImportController::class, 'showMapping'])->name('products.import.mapping');
        Route::post('/products/import/{import}/mapping',        [ProductImportController::class, 'storeMapping'])->name('products.import.mapping.store');

        Route::resource('products', ProductController::class)
            ->except(['show'])
            ->parameters(['products' => 'product']);

        // --- Listas de precios ---
        Route::resource('price-lists', PriceListController::class)
            ->except(['show'])
            ->parameters(['price-lists' => 'priceList']);
        Route::post('/price-lists/{priceList}/prices', [PriceListController::class, 'savePrices'])
            ->name('price-lists.prices');

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
    });
