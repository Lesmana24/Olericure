<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PlantScanController;

// ==========================================
// 1. AREA PUBLIK (TIDAK BUTUH LOGIN)
// ==========================================
Route::get('/', function () {
    $title = 'Selamat Datang';
    $slug  = 'welcome';
    return view('konten.welcome', compact('title', 'slug'));
});

// Middleware 'guest' mencegah user yang SUDAH login balik lagi ke form
Route::middleware('guest:pengguna')->group(function () {
    Route::get('/daftar', [PenggunaController::class, 'create'])->name('daftar');
    // Limit Daftar: Maksimal 5x coba dalam 1 menit (Cegah spam akun)
    Route::post('/daftar', [PenggunaController::class, 'store'])->middleware('throttle:5,1');

    Route::get('/login',  [LoginController::class, 'create'])->name('login');
    // Limit Login: Maksimal 5x coba dalam 1 menit (Cegah Brute Force)
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:5,1');
});

// ==========================================
// 2. AREA PRIVAT (WAJIB LOGIN)
// ==========================================
Route::middleware('auth:pengguna')->group(function () {
    
    // Akses Akun & Homepage
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    
    // Limit Update Setting: Maksimal 30x dalam 1 menit (Cegah ESP32 nge-hang karena dispam MQTT)
    Route::post('/update-setting', [HomeController::class, 'updateSettings'])
        ->name('update.setting')
        ->middleware('throttle:30,1');

    // Notifikasi
    Route::get('/notification', [NotificationController::class, 'index']);
    Route::delete('/notification/clear', [NotificationController::class, 'deleteAll'])->name('notification.clear');

    // ------------------------------------------
    // FITUR UTAMA AI (Semuanya Wajib Login!)
    // ------------------------------------------
    Route::get('/ai', [PlantScanController::class, 'index'])->name('ai.index');
    
    // Limit Upload Gambar: Maksimal 10x dalam 1 menit (Cegah server penuh)
    Route::post('/ai/upload', [PlantScanController::class, 'upload'])
        ->name('ai.upload')
        ->middleware('throttle:10,1');
    
    // Alur Hasil Deteksi
    Route::get('/ai/result/preview', [PlantScanController::class, 'preview'])->name('ai.preview');
    Route::post('/ai/result/store', [PlantScanController::class, 'storeReport'])->name('ai.store');
    Route::post('/ai/result/reset', [PlantScanController::class, 'reset'])->name('ai.reset');
    Route::get('/ai/result/{id}', [PlantScanController::class, 'result'])->name('ai.result');
    
    // Hapus Riwayat Deteksi
    Route::delete('/ai/history/{id}', [PlantScanController::class, 'destroy'])->name('ai.history.destroy');

    // Limit Maksimal 15 request dalam 1 menit (Amankan kuota API lu!)
    Route::post('/api/chat-botanist', [PlantScanController::class, 'chatBotanist'])
        ->name('ai.chat')
        ->middleware('throttle:15,1');

});
