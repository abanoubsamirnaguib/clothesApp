<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DiscountController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\TryOnController;
use App\Models\Setting;
use Illuminate\Support\Facades\Route;

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{slug}', [ProductController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::post('/orders', [OrderController::class, 'store']);
Route::post('/discounts/validate', [DiscountController::class, 'validateCode']);

Route::get('/tryon/eligibility', [TryOnController::class, 'eligibility']);
Route::get('/tryon/result', [TryOnController::class, 'result']);
Route::post('/tryon/reserve', [TryOnController::class, 'reserve']);
Route::post('/tryon/complete', [TryOnController::class, 'complete']);
Route::get('/tryon/products/{productId}/best-image', [TryOnController::class, 'bestGarmentImage']);

Route::get('/settings', function () {
    return response()->json([
        'store_name' => Setting::value('store_name', 'NuxtCommerce'),
        'currency' => Setting::value('currency', 'USD'),
        'currency_symbol' => Setting::value('currency_symbol', '$'),
        'whatsapp_number' => Setting::value('whatsapp_number', ''),
    ]);
});
