<?php

use Illuminate\Support\Facades\Route;
use Skeleton\Store\Controllers\Backend\Plans\PlansController;
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
        Route::get('/edit/product/{product}', 'edit')->name('admin.store.product.edit');
        Route::patch('/update/product/{product}', 'update')->name('admin.store.product.update');
    });

    Route::controller(PlansController::class)->group(function () {
        Route::get('/store/plans', 'index')->name('admin.store.plans.index');
    });
});
