<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CafeSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeliveryOrderController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\PosTableController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/tables/state', [DashboardController::class, 'state'])->name('tables.state');
    Route::post('/tables/{posTable}/sessions', [CafeSessionController::class, 'open'])->name('tables.open');

    Route::get('/sessions/{cafeSession}', [CafeSessionController::class, 'show'])->name('sessions.show');
    Route::get('/sessions/{cafeSession}/state', [CafeSessionController::class, 'state'])->name('sessions.state');
    Route::patch('/sessions/{cafeSession}/billing', [CafeSessionController::class, 'markBilling'])->name('sessions.billing');
    Route::post('/sessions/{cafeSession}/close', [CafeSessionController::class, 'close'])->name('sessions.close');
    Route::post('/sessions/{cafeSession}/cancel', [CafeSessionController::class, 'cancel'])->name('sessions.cancel');
    Route::get('/sessions/{cafeSession}/invoice', [CafeSessionController::class, 'invoice'])->name('sessions.invoice');

    Route::post('/sessions/{cafeSession}/orders', [OrderItemController::class, 'store'])->name('orders.store');
    Route::patch('/orders/{orderItem}', [OrderItemController::class, 'update'])->name('orders.update');
    Route::delete('/orders/{orderItem}', [OrderItemController::class, 'destroy'])->name('orders.destroy');

    Route::get('/delivery-orders', [DeliveryOrderController::class, 'index'])->name('delivery-orders.index');
    Route::post('/delivery-orders', [DeliveryOrderController::class, 'store'])->name('delivery-orders.store');
    Route::patch('/delivery-orders/{deliveryOrder}', [DeliveryOrderController::class, 'update'])->name('delivery-orders.update');
    Route::delete('/delivery-orders/{deliveryOrder}', [DeliveryOrderController::class, 'destroy'])->name('delivery-orders.destroy');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');

    Route::middleware('admin')->group(function (): void {
        Route::post('/tables', [PosTableController::class, 'store'])->name('tables.store');
        Route::patch('/tables/{posTable}', [PosTableController::class, 'update'])->name('tables.update');
        Route::delete('/tables/{posTable}', [PosTableController::class, 'destroy'])->name('tables.destroy');
        Route::resource('menu-items', MenuItemController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('/reports/daily', [ReportController::class, 'daily'])->name('reports.daily');
    });
});
