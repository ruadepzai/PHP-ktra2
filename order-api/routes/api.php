<?php

use Illuminate\Support\Facades\Route;
// 1. Đã sửa lại đường dẫn import (thêm \Api\)
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrderController;

// Nhóm 1: Auth Routes (Không cần Token)
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
});

// Nhóm 2: Các Routes bắt buộc phải có Token mới được dùng
Route::middleware('jwt.auth')->group(function () {
    // Tài khoản
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me', [AuthController::class, 'me']);

    // 2. Đã bổ sung route refresh token theo yêu cầu
    Route::post('auth/refresh', [AuthController::class, 'refresh']);

    // Đơn hàng
    Route::post('orders', [OrderController::class, 'store']);
    Route::get('orders', [OrderController::class, 'index']);
    Route::get('orders/my', [OrderController::class, 'myOrders']);
    Route::get('orders/{id}', [OrderController::class, 'show']);
    Route::put('orders/{id}', [OrderController::class, 'update']);
    Route::patch('orders/{id}/confirm', [OrderController::class, 'confirmOrder']);
    Route::patch('orders/{id}/cancel', [OrderController::class, 'cancelOrder']);
    Route::delete('orders/{id}', [OrderController::class, 'destroy']);
});
