<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\CartController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\ShopController;
use App\Http\Controllers\API\WishlistController;
use App\Http\Controllers\API\UserController;

Route::prefix('v1')->group(function () {

    // Auth
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/signup', [AuthController::class, 'signup']);

    // Public
    Route::get('/products', [ProductController::class, 'products_list']);
    Route::get('/products/{id}', [ProductController::class, 'products_show']);

    // Protected
    Route::middleware('jwt.auth')->group(function () {
        // users
        Route::get('/user', [UserController::class, 'user']);

        // seller routes
        Route::post('/product-add', [ProductController::class, 'add']);

        //  user routes
        Route::get('/cart', [CartController::class, 'list']);
        Route::post('/cart-add', [CartController::class, 'add']);
        Route::post('/cart/edit', [CartController::class, 'edit']);
        Route::post('/cart/delete', [CartController::class, 'remove']);

        Route::post('/orders-add', [OrderController::class, 'store']);
        Route::get('/orders', [OrderController::class, 'myOrders']);

        Route::post('/wishlist-add', [WishlistController::class, 'add']);
        Route::get('/wishlist', [WishlistController::class, 'list']);
        Route::post('/wishlist/remove', [WishlistController::class, 'destroy']);
    });

});

