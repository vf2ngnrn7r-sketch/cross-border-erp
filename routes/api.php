<?php

use App\Http\Controllers\Api\V1\ResourceApiController;
use Illuminate\Support\Facades\Route;

/**
 * JSON API
 * 说明：默认 api 中间件不含 session。本脚手架同时在 web.php 挂载了
 * /api/v1/*（auth session）便于本地演示；此处提供无状态入口占位，
 * 后续可接入 Sanctum / Passport token。
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

// 若已登录（例如通过 stateful 客户端），可使用下列路由
Route::middleware('web', 'auth')->prefix('v1')->group(function () {
    Route::get('products', [ResourceApiController::class, 'products']);
    Route::post('products', [ResourceApiController::class, 'storeProduct']);
    Route::get('orders', [ResourceApiController::class, 'orders']);
    Route::get('orders/{order}', [ResourceApiController::class, 'order']);
    Route::get('inventories', [ResourceApiController::class, 'inventories']);
    Route::get('listings', [ResourceApiController::class, 'listings']);
    Route::get('amazon-accounts', [ResourceApiController::class, 'amazonAccounts']);
    Route::get('settlements', [ResourceApiController::class, 'settlements']);
    Route::get('campaigns', [ResourceApiController::class, 'campaigns']);
    Route::get('returns', [ResourceApiController::class, 'returns']);
    Route::get('sync-jobs', [ResourceApiController::class, 'syncJobs']);
});
