<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderWebController;

// Tự động chuyển hướng từ trang chủ sang trang danh sách đơn
Route::get('/', function () {
    return redirect('/orders');
});

// Các Routes chính thức cho giao diện Web (Task B.4)
Route::get('/orders', [OrderWebController::class, 'index']);
Route::get('/orders/{id}', [OrderWebController::class, 'show']);