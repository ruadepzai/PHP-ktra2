<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;

// Auth Routes
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
Route::post('logout', [AuthController::class, 'logout'])->middleware('jwt.auth');
Route::get('me', [AuthController::class, 'me'])->middleware('jwt.auth');

// Product Routes (Tự động sinh ra 5 routes: get all, get 1, post, put, delete)
Route::apiResource('products', ProductController::class);

// Order Routes
Route::get('orders', [OrderController::class, 'index'])->middleware('jwt.auth');
Route::post('orders', [OrderController::class, 'store'])->middleware('jwt.auth');