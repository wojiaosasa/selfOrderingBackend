<?php

use App\Http\Controllers\Auth\WechatLoginController;
use App\Http\Controllers\ChefBindingController;
use App\Http\Controllers\ChefDishController;
use App\Http\Controllers\ChefOrderController;
use App\Http\Controllers\ChefProfileController;
use App\Http\Controllers\DinerBindingController;
use App\Http\Controllers\DinerChefController;
use App\Http\Controllers\DinerOrderController;
use App\Http\Controllers\DishImageUploadController;
use App\Http\Controllers\MeController;
use Illuminate\Support\Facades\Route;

Route::post('/wechat/login', WechatLoginController::class)->name('api.wechat.login');

Route::middleware('api.token')->group(function (): void {
    Route::get('/me', [MeController::class, 'show'])->name('api.me.show');
    Route::put('/me/profile', [MeController::class, 'updateProfile'])->name('api.me.profile.update');
    Route::post('/me/role', [MeController::class, 'selectRole'])->name('api.me.role.store');

    Route::prefix('chef')->name('api.chef.')->group(function (): void {
        Route::get('/profile', [ChefProfileController::class, 'show'])->name('profile.show');
        Route::put('/profile', [ChefProfileController::class, 'update'])->name('profile.update');
        Route::get('/dishes', [ChefDishController::class, 'index'])->name('dishes.index');
        Route::post('/dishes', [ChefDishController::class, 'store'])->name('dishes.store');
        Route::put('/dishes/{dish}', [ChefDishController::class, 'update'])->name('dishes.update');
        Route::patch('/dishes/{dish}/status', [ChefDishController::class, 'updateStatus'])->name('dishes.status');
        Route::delete('/dishes/{dish}', [ChefDishController::class, 'destroy'])->name('dishes.destroy');
        Route::get('/bindings', [ChefBindingController::class, 'index'])->name('bindings.index');
        Route::get('/orders', [ChefOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [ChefOrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{order}/accept', [ChefOrderController::class, 'accept'])->name('orders.accept');
        Route::post('/orders/{order}/reject', [ChefOrderController::class, 'reject'])->name('orders.reject');
        Route::post('/orders/{order}/complete', [ChefOrderController::class, 'complete'])->name('orders.complete');
    });

    Route::prefix('diner')->name('api.diner.')->group(function (): void {
        Route::post('/bindings', [DinerBindingController::class, 'store'])->name('bindings.store');
        Route::get('/chefs', [DinerChefController::class, 'index'])->name('chefs.index');
        Route::get('/chefs/{chef}/menu', [DinerChefController::class, 'menu'])->name('chefs.menu');
        Route::post('/orders', [DinerOrderController::class, 'store'])->name('orders.store');
        Route::get('/orders', [DinerOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [DinerOrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{order}/cancel', [DinerOrderController::class, 'cancel'])->name('orders.cancel');
    });

    Route::post('/uploads/dish-image', DishImageUploadController::class)->name('api.uploads.dish-image.store');
});
