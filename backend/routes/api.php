<?php
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ShopController;
use Illuminate\Support\Facades\Route;

Route::get('/categories', [ShopController::class, 'categories']);
Route::get('/products', [ShopController::class, 'products']);
Route::get('/products/{slug}', [ShopController::class, 'product']);
Route::get('/orders/{number}', [ShopController::class, 'showOrder']);
Route::post('/orders', [ShopController::class, 'order'])->middleware('throttle:30,1');
Route::post('/qpay/create', [ShopController::class, 'createQpay']);
Route::post('/qpay/check', [ShopController::class, 'checkQpay']);
Route::post('/payments/{payment}/proof', [ShopController::class, 'uploadProof'])->middleware('throttle:10,1');
Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::prefix('admin')->group(function () {
        Route::get('/products', [ShopController::class, 'adminProducts']);
        Route::post('/products', [ShopController::class, 'saveProduct']);
        Route::put('/products/{product}', [ShopController::class, 'saveProduct']);
        Route::delete('/products/{product}', [ShopController::class, 'deleteProduct']);
        Route::get('/orders', [ShopController::class, 'adminOrders']);
        Route::put('/orders/{order}', [ShopController::class, 'updateOrder']);
        Route::put('/payments/{payment}', [ShopController::class, 'confirmPayment']);
    });
});