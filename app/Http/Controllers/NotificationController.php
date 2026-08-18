<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Controller khusus melayani antarmuka Web Blade Dashboard.
 */
class NotificationController extends Controller
{
    /**
     * 1. Tampilkan Halaman Notifikasi (Web Dashboard).
     */
    public function index(): View
    {
        $notifications = Notification::latest('created_at')->get();

        return view('konten.notification', [
            'title' => 'Notifikasi',
            'notifications' => $notifications,
        ]);
    }

    /**
     * 2. Hapus Semua Notifikasi (Web Action).
     */
    public function deleteAll(): RedirectResponse
    {
        Notification::query()->delete();

        return redirect()->back()->with('success', 'Semua notifikasi berhasil dihapus.');
    }
}
