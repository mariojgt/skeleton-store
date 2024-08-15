<?php

use Illuminate\Support\Facades\Route;
use Skeleton\Store\Controllers\Backend\Product\ProductController;
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
    });
});
