<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BrandController as AdminBrandController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\IntegrationController;
use App\Http\Controllers\Admin\MediaAssetController as AdminMediaAssetController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\StorefrontController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StorefrontController::class, 'index'])->name('storefront.index');

Route::get('/login', [AuthController::class, 'create'])->name('login');
Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
Route::post('/logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function (): void {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('products', AdminProductController::class)->except('show');
    Route::resource('categories', AdminCategoryController::class)->except('show');

    Route::get('/brands', [AdminBrandController::class, 'index'])->name('brands.index');
    Route::post('/brands', [AdminBrandController::class, 'store'])->name('brands.store');
    Route::put('/brands/{brand}', [AdminBrandController::class, 'update'])->name('brands.update');
    Route::delete('/brands/{brand}', [AdminBrandController::class, 'destroy'])->name('brands.destroy');

    Route::get('/media-assets', [AdminMediaAssetController::class, 'index'])->name('media-assets.index');
    Route::post('/media-assets', [AdminMediaAssetController::class, 'store'])->name('media-assets.store');
    Route::patch('/media-assets/{mediaAsset}/status', [AdminMediaAssetController::class, 'updateStatus'])->name('media-assets.status');
    Route::post('/media-assets/{mediaAsset}/products', [AdminMediaAssetController::class, 'attachProduct'])->name('media-assets.products.attach');
    Route::delete('/media-assets/{mediaAsset}/products/{product}', [AdminMediaAssetController::class, 'detachProduct'])->name('media-assets.products.detach');
    Route::post('/media-assets/{mediaAsset}/categories', [AdminMediaAssetController::class, 'attachCategory'])->name('media-assets.categories.attach');
    Route::delete('/media-assets/{mediaAsset}/categories/{category}', [AdminMediaAssetController::class, 'detachCategory'])->name('media-assets.categories.detach');

    Route::get('/integrations', [IntegrationController::class, 'index'])->name('integrations.index');
    Route::post('/integrations/tokens', [IntegrationController::class, 'storeToken'])->name('integrations.tokens.store');
    Route::delete('/integrations/tokens/{token}', [IntegrationController::class, 'destroyToken'])->name('integrations.tokens.destroy');
});
