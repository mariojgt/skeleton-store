<?php

use Illuminate\Support\Facades\Route;
use Skeleton\Store\Controllers\Backend\Plans\PlansController;
use Skeleton\Store\Controllers\Backend\Product\ProductController;
use Skeleton\Store\Controllers\Backend\Settings\StoreSettingsController;
use Skeleton\Store\Controllers\Backend\Product\ProductResourceController;
use Skeleton\Store\Controllers\Backend\ProductCategory\ProductCategoryController;

// Standard
Route::group([
    'middleware' => ['web'],
    'prefix'     => 'admin',
], function () {
    // Add your normal routes in here
    Route::controller(ProductCategoryController::class)->group(function () {
        Route::get('/store/product-category', 'index')->name('admin.store.product-category.index');
    });

    Route::controller(ProductController::class)->group(function () {
        Route::get('/store/product', 'index')->name('admin.store.product.index');
        Route::get('/edit/product/{product}', 'edit')->name('admin.store.product.edit');
        Route::patch('/update/product/{product}', 'update')->name('admin.store.product.update');
    });

    Route::controller(PlansController::class)->group(function () {
        Route::get('/store/plans', 'index')->name('admin.store.plans.index');
    });

    Route::controller(StoreSettingsController::class)->group(function () {
        Route::get('/store/settings', 'index')->name('admin.store.settings.index');
    });

    Route::prefix('admin/store/product/{product}/resources')->name('admin.store.product.resources.')->group(function () {
        Route::get('/', [ProductResourceController::class, 'index'])->name('index');
        Route::post('/', [ProductResourceController::class, 'store'])->name('store');
        Route::put('/{resource}', [ProductResourceController::class, 'update'])->name('update');
        Route::delete('/{resource}', [ProductResourceController::class, 'destroy'])->name('destroy');
    });
});
