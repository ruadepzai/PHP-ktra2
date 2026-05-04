<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;

// Nhóm 1: Auth Routes (Không cần Token) - Tổng: 2 routes
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
});

// Nhóm 2: Các Routes bắt buộc phải có Token mới được dùng - Tổng: 9 routes
Route::middleware('jwt.auth')->group(function () {
    // Tài khoản
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me', [AuthController::class, 'me']);

    // Đơn hàng
    Route::post('orders', [OrderController::class, 'store']);
    Route::get('orders', [OrderController::class, 'index']);
    Route::get('orders/my', [OrderController::class, 'myOrders']);
    Route::get('orders/{id}', [OrderController::class, 'show']);
    Route::put('orders/{id}', [OrderController::class, 'update']);
    Route::patch('orders/{id}/confirm', [OrderController::class, 'confirm']);
    Route::patch('orders/{id}/cancel', [OrderController::class, 'cancel']);
});