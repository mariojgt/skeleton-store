<?php

use Illuminate\Support\Facades\Route;
use Skeleton\Store\Controllers\Backend\Plans\PlansController;
use Skeleton\Store\Controllers\Backend\Product\ProductController;
use Skeleton\Store\Controllers\Backend\Settings\StoreSettingsController;
use Skeleton\Store\Controllers\Backend\Capabilities\CapabilityController;
use Skeleton\Store\Controllers\Backend\Product\ProductResourceController;
use Skeleton\Store\Controllers\Backend\Subscriptions\SubscriptionController;
use Skeleton\Store\Controllers\Backend\ProductCategory\ProductCategoryController;

// Standard
Route::group([
    'middleware' => ['skeleton_admin', '2fa:skeleton_admin'],
    'prefix'     => config('skeleton.route_prefix'),
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

    Route::controller(SubscriptionController::class)->group(function () {
        Route::get('/store/subscriptions', 'index')->name('admin.store.subscriptions.index');
        Route::get('/store/subscriptions/list', 'list')->name('admin.store.subscriptions.list');
        Route::post('/store/subscriptions', 'store')->name('admin.store.subscriptions.store');
        Route::get('/store/subscriptions/{id}', 'show')->name('admin.store.subscriptions.show');
        Route::put('/store/subscriptions/{id}/status', 'updateStatus')->name('admin.store.subscriptions.update-status');
        Route::put('/store/subscriptions/{id}/plan', 'changePlan')->name('admin.store.subscriptions.change-plan');
        Route::put('/store/subscriptions/{id}/extend', 'extend')->name('admin.store.subscriptions.extend');
        Route::put('/store/subscriptions/{id}/toggle-renew', 'toggleAutoRenew')->name('admin.store.subscriptions.toggle-renew');
        Route::put('/store/subscriptions/{id}/cancel', 'cancel')->name('admin.store.subscriptions.cancel');
        Route::get('/store/subscriptions/search/users', 'searchUsers')->name('admin.store.subscriptions.search-users');
    });

    Route::controller(CapabilityController::class)->group(function () {
        // Get all capabilities
        Route::get('/store/capabilities/list', 'list')->name('admin.store.capabilities.list');
        // Get user capabilities for a subscription
        Route::get('/store/capabilities/user/{subscription}', 'getUserCapabilities')->name('admin.store.capabilities.user');
        // Manage capabilities
        Route::post('/store/capabilities/add', 'addCapability')->name('admin.store.capabilities.add');
        Route::put('/store/capabilities/{capability}', 'updateCapability')->name('admin.store.capabilities.update');
        Route::delete('/store/capabilities/{capability}', 'removeCapability')->name('admin.store.capabilities.remove');
    });

    Route::prefix('admin/store/product/{product}/resources')->name('admin.store.product.resources.')->group(function () {
        Route::get('/', [ProductResourceController::class, 'index'])->name('index');
        Route::post('/', [ProductResourceController::class, 'store'])->name('store');
        Route::put('/{resource}', [ProductResourceController::class, 'update'])->name('update');
        Route::delete('/{resource}', [ProductResourceController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('admin/store/product/{product}/resources')->name('admin.store.product.resources.')->group(function () {
        Route::get('/', [ProductResourceController::class, 'index'])->name('index');
        Route::post('/', [ProductResourceController::class, 'store'])->name('store');
        Route::put('/{resource}', [ProductResourceController::class, 'update'])->name('update');
        Route::delete('/{resource}', [ProductResourceController::class, 'destroy'])->name('destroy');
    });
});
