<?php

use App\Http\Controllers\Admin\AdvertisingController;
use App\Http\Controllers\Admin\AmazonAccountController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FinanceController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\ListingController;
use App\Http\Controllers\Admin\MessagingController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PurchasingController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ReturnController;
use App\Http\Controllers\Admin\SyncJobController;
use App\Http\Controllers\Admin\WarehouseController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('admin.dashboard')
        : redirect()->route('login');
});

Route::get('/dashboard', function () {
    return redirect()->route('admin.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');

        Route::get('amazon-accounts', [AmazonAccountController::class, 'index'])->name('amazon-accounts.index');
        Route::get('amazon-accounts/create', [AmazonAccountController::class, 'create'])->name('amazon-accounts.create');
        Route::post('amazon-accounts', [AmazonAccountController::class, 'store'])->name('amazon-accounts.store');
        Route::get('amazon-accounts/{amazonAccount}', [AmazonAccountController::class, 'show'])->name('amazon-accounts.show');
        Route::post('amazon-accounts/{amazonAccount}/sync', [AmazonAccountController::class, 'sync'])->name('amazon-accounts.sync');

        Route::resource('products', ProductController::class);

        Route::get('listings', [ListingController::class, 'index'])->name('listings.index');
        Route::get('listings/{listing}', [ListingController::class, 'show'])->name('listings.show');
        Route::get('inventories', [InventoryController::class, 'index'])->name('inventories.index');

        Route::get('purchasing', [PurchasingController::class, 'index'])->name('purchasing.index');
        Route::get('purchasing/{purchaseOrder}', [PurchasingController::class, 'show'])->name('purchasing.show');

        Route::get('warehouse', [WarehouseController::class, 'index'])->name('warehouse.index');
        Route::get('warehouse/{shipment}', [WarehouseController::class, 'show'])->name('warehouse.show');

        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');

        Route::get('finance', [FinanceController::class, 'index'])->name('finance.index');
        Route::get('finance/events', [FinanceController::class, 'events'])->name('finance.events');
        Route::get('finance/{settlement}', [FinanceController::class, 'show'])->name('finance.show');

        Route::get('advertising', [AdvertisingController::class, 'index'])->name('advertising.index');
        Route::get('advertising/{campaign}', [AdvertisingController::class, 'show'])->name('advertising.show');

        Route::get('messaging', [MessagingController::class, 'index'])->name('messaging.index');

        Route::get('returns', [ReturnController::class, 'index'])->name('returns.index');

        Route::get('sync-jobs', [SyncJobController::class, 'index'])->name('sync-jobs.index');
        Route::get('sync-jobs/{syncJob}', [SyncJobController::class, 'show'])->name('sync-jobs.show');
    });
});

require __DIR__.'/auth.php';
