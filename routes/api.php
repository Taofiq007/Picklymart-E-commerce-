<?php

use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CategoryMediaController;
use App\Http\Controllers\Api\MediaAssetController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductMediaController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::apiResource('products', ProductController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::post('/products/{product}/media', [ProductMediaController::class, 'store']);
    Route::delete('/products/{product}/media/{mediaAsset}', [ProductMediaController::class, 'destroy']);

    Route::apiResource('categories', CategoryController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::post('/categories/{category}/media', [CategoryMediaController::class, 'store']);
    Route::delete('/categories/{category}/media/{mediaAsset}', [CategoryMediaController::class, 'destroy']);

    Route::apiResource('brands', BrandController::class);
    Route::apiResource('media-assets', MediaAssetController::class)->only(['index', 'store', 'show']);
});
