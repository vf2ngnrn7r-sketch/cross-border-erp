<?php

use App\Http\Controllers\Api\V1\ResourceApiController;
use Illuminate\Support\Facades\Route;

/**
 * JSON API — session auth for local demo; Sanctum can replace later.
 */
Route::prefix('v1')->group(function () {
    Route::get('health', fn () => response()->json([
        'ok' => true,
        'app' => config('app.name'),
        'amazon_driver' => config('amazon.driver'),
        'amazon_stub' => config('amazon.stub'),
        'fixtures_path' => config('amazon.fixtures.path'),
    ]));
});

Route::middleware('web', 'auth')->prefix('v1')->group(function () {
    Route::get('products', [ResourceApiController::class, 'products']);
    Route::post('products', [ResourceApiController::class, 'storeProduct']);
    Route::get('products/{product}', [ResourceApiController::class, 'product']);
    Route::get('products/{product}/variants', [ResourceApiController::class, 'productVariants']);
    Route::get('categories', [ResourceApiController::class, 'categories']);
    Route::get('orders', [ResourceApiController::class, 'orders']);
    Route::get('orders/{order}', [ResourceApiController::class, 'order']);
    Route::get('inventories', [ResourceApiController::class, 'inventories']);
    Route::get('listings', [ResourceApiController::class, 'listings']);
    Route::get('listings/{listing}', [ResourceApiController::class, 'listing']);
    Route::get('amazon-accounts', [ResourceApiController::class, 'amazonAccounts']);
    Route::get('settlements', [ResourceApiController::class, 'settlements']);
    Route::get('campaigns', [ResourceApiController::class, 'campaigns']);
    Route::get('ad-metrics', [ResourceApiController::class, 'adMetrics']);
    Route::get('returns', [ResourceApiController::class, 'returns']);
    Route::get('sync-jobs', [ResourceApiController::class, 'syncJobs']);
    Route::get('suppliers', [ResourceApiController::class, 'suppliers']);
    Route::get('purchase-orders', [ResourceApiController::class, 'purchaseOrders']);
    Route::get('warehouses', [ResourceApiController::class, 'warehouses']);
    Route::get('shipments', [ResourceApiController::class, 'shipments']);
    Route::get('messages', [ResourceApiController::class, 'messages']);
    Route::get('cases', [ResourceApiController::class, 'cases']);
    Route::get('product-qualifications', [ResourceApiController::class, 'productQualifications']);
    Route::post('product-qualifications', [ResourceApiController::class, 'storeProductQualification']);
    Route::get('product-qualifications/{productQualification}', [ResourceApiController::class, 'productQualification']);
    Route::get('store-qualifications', [ResourceApiController::class, 'storeQualifications']);
    Route::post('store-qualifications', [ResourceApiController::class, 'storeStoreQualification']);
    Route::get('store-qualifications/{storeQualification}', [ResourceApiController::class, 'storeQualification']);
    Route::get('reports/summary', [ResourceApiController::class, 'reportSummary']);
});
