<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderWebController;

// Tự động chuyển hướng từ trang chủ sang trang danh sách đơn
Route::get('/', function () {
    return redirect('/orders');
});

// Các Routes chính thức cho giao diện Web (Task B.4)
Route::get('/orders', [OrderWebController::class, 'index'])->name('orders.index');
Route::get('/orders/{id}', [OrderWebController::class, 'show'])->name('orders.show');
Route::get('/orders/{id}/edit', [OrderWebController::class, 'edit'])->name('orders.edit');
Route::delete('/orders/{id}', [OrderWebController::class, 'destroy'])->name('orders.destroy');
Route::patch('/orders/{id}/confirm', [OrderWebController::class, 'confirm'])->name('orders.confirm');
Route::patch('/orders/{id}/cancel', [OrderWebController::class, 'cancel'])->name('orders.cancel');
