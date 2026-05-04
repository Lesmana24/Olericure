<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\NotificationApiController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PlantScanController;

// Jalur Bebas (Nggak butuh token)
Route::middleware('throttle:5,1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/settings', [SettingController::class, 'getSettings']);
    Route::post('/update-setting', [SettingController::class, 'update']);
    Route::get('/notifications', [NotificationApiController::class, 'getNotifications']);
    Route::delete('/notifications/clear', [NotificationApiController::class, 'clearNotifications']);
    
    // Fitur AI
    Route::post('/ai/upload', [PlantScanController::class, 'upload']);
    Route::post('/ai/store', [PlantScanController::class, 'storeReport']);
    Route::post('/mobile-chat', [PlantScanController::class, 'chatBotanist']);
    Route::get('/ai/history', [PlantScanController::class, 'historyApi']);
    Route::delete('/ai/history/{id}', [PlantScanController::class, 'destroy']);

});
Route::post('/simpan-notif', [NotificationController::class, 'storeLog']);
