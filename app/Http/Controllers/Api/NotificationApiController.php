<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;

class NotificationApiController extends Controller
{
    // 1. Ambil semua notifikasi buat ditampilin di Flutter
    public function getNotifications()
    {
        // Ambil data terbaru paling atas, mirip kayak di web
        $notifications = Notification::orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $notifications
        ], 200);
    }

    // 2. Hapus semua notifikasi dari tombol "Reset" di Flutter
    public function clearNotifications()
    {
        Notification::truncate();

        return response()->json([
            'success' => true,
            'message' => 'Semua notifikasi berhasil dihapus'
        ], 200);
    }
}
