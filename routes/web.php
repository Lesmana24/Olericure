<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\PlantScanController;
use Illuminate\Support\Facades\Route;

// ==========================================
// 1. AREA PUBLIK (TIDAK BUTUH LOGIN)
// ==========================================
Route::get('/', function () {
    $title = 'Selamat Datang';
    $slug = 'welcome';

    return view('konten.welcome', compact('title', 'slug'));
});

// Middleware 'guest' mencegah user yang SUDAH login balik lagi ke form
Route::middleware('guest:pengguna')->group(function () {
    Route::get('/daftar', [PenggunaController::class, 'create'])->name('daftar');
    // Limit Daftar: Maksimal 5x coba dalam 1 menit (Cegah spam akun)
    Route::post('/daftar', [PenggunaController::class, 'store'])->middleware('throttle:5,1');

    Route::get('/login', [LoginController::class, 'create'])->name('login');
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

    // Limit Maksimal 15 request dalam 1 menit
    Route::post('/api/chat-botanist', [PlantScanController::class, 'chatBotanist'])
        ->name('ai.chat')
        ->middleware('throttle:15,1');

});

// ============================================================
// SHARED HOSTING UTILITIES (InfinityFree / Tanpa SSH)
// ============================================================

// Nuclear Cache Cleaner - Hapus SEMUA cache file secara manual
// Kunjungi: agrosquad.page.gd/clear-cache
Route::get('/clear-cache', function () {
    $report = [];
    $deleted = 0;

    // STEP 1: Jalankan Artisan commands (mungkin gagal di beberapa host)
    $artisanCommands = ['config:clear', 'route:clear', 'view:clear', 'cache:clear', 'event:clear'];
    foreach ($artisanCommands as $cmd) {
        try {
            \Illuminate\Support\Facades\Artisan::call($cmd);
            $report[] = "✅ artisan {$cmd} → OK";
        } catch (\Throwable $e) {
            $report[] = "⚠️ artisan {$cmd} → Gagal (".$e->getMessage().')';
        }
    }

    // STEP 2: Hapus file cache secara manual sebagai fallback
    $cacheDirs = [
        base_path('bootstrap/cache') => '*.php',
        storage_path('framework/views') => '*.php',
        storage_path('framework/cache/data') => '*',
    ];

    foreach ($cacheDirs as $dir => $pattern) {
        if (! is_dir($dir)) {
            $report[] = "📁 Skip: {$dir} (tidak ditemukan)";

            continue;
        }
        $files = glob($dir.DIRECTORY_SEPARATOR.$pattern);
        foreach ($files as $file) {
            $basename = basename($file);
            // Jangan hapus file .gitignore
            if (is_file($file) && $basename !== '.gitignore') {
                @unlink($file);
                $deleted++;
            }
        }
        $report[] = "🗑️ Cleaned: {$dir}";
    }

    $summary = "<h2>✅ Cache Dibersihkan! ({$deleted} file dihapus)</h2>";
    $summary .= "<p>Silakan <a href='/'>refresh halaman web</a> Anda.</p>";
    $summary .= '<hr><h3>Detail:</h3><ul>';
    foreach ($report as $line) {
        $summary .= "<li>{$line}</li>";
    }
    $summary .= '</ul>';

    return response($summary)->header('Content-Type', 'text/html');
});

// Debug Route - Periksa path file gambar di server
// Kunjungi: agrosquad.page.gd/debug-storage
Route::get('/debug-storage', function () {
    $disk = \Illuminate\Support\Facades\Storage::disk('public');
    $diskRoot = $disk->path('');
    $diskUrl = $disk->url('test.jpg');
    $files = [];
    try {
        $files = $disk->allFiles();
    } catch (\Throwable $e) {
        $files = ['Error: '.$e->getMessage()];
    }

    return response()->json([
        'public_path' => public_path(),
        'storage_path' => storage_path(),
        'disk_root' => $diskRoot,
        'disk_url_test' => $diskUrl,
        'app_url' => config('app.url'),
        'all_files' => array_slice($files, 0, 20),
    ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
});

// Route langsung untuk melayani gambar via /uploads/ (100% bekerja tanpa symlink)
Route::get('/uploads/{path}', function ($path) {
    $filePath = public_path("uploads/{$path}");
    if (file_exists($filePath)) {
        return response()->file($filePath);
    }
    abort(404, "File tidak ditemukan: {$filePath}");
})->where('path', '.*');

// Route fallback /storage/{path} → redirect ke /uploads/ (intercept URL lama)
Route::get('/storage/{path}', function ($path) {
    // Prioritas 1: Cek di public/uploads (disk baru)
    $uploadPath = public_path("uploads/{$path}");
    if (file_exists($uploadPath)) {
        return response()->file($uploadPath);
    }
    // Prioritas 2: Cek di storage/app/public (disk lama)
    $storagePath = storage_path("app/public/{$path}");
    if (file_exists($storagePath)) {
        return response()->file($storagePath);
    }
    abort(404, 'Gambar tidak ditemukan di kedua lokasi.');
})->where('path', '.*');
