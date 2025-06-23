<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// 定義一個 POST 路由來計算訂單價格
Route::post('/calculate-order-price', [OrderController::class, 'calculatePrice']);

// 如果你實作了通知裝飾器，也可以在這裡定義一個通知發送的 API
// Route::post('/send-notification', [NotificationController::class, 'send']);
