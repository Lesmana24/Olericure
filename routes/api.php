<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\NotificationApiController;

// Jalur Bebas (Nggak butuh token)
Route::middleware('throttle:5,1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Jalur Ketat (Wajib bawa Token dari hasil Login/Register)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/settings', [SettingController::class, 'getSettings']);
    Route::post('/update-setting', [SettingController::class, 'update']);
    Route::get('/notifications', [NotificationApiController::class, 'getNotifications']);
    Route::delete('/notifications/clear', [NotificationApiController::class, 'clearNotifications']);
});
