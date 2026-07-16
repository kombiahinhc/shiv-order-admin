<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ShopController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/shops', [ShopController::class, 'index']);
    Route::post('/shops', [ShopController::class, 'store']);
    Route::post('/shops/{shop}/approve', [ShopController::class, 'approve']);

    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/my-orders', [OrderController::class, 'myOrders']);
    Route::post('/orders', [OrderController::class, 'store']);
});
