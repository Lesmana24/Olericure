<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNotificationRequest;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;

/**
 * Controller API khusus melayani Mobile App (Flutter) & Hardware Telemetry (ESP32).
 */
class NotificationApiController extends Controller
{
    /**
     * 1. Ambil daftar notifikasi terbaru untuk Mobile Flutter App.
     */
    public function getNotifications(): JsonResponse
    {
        $notifications = Notification::latest('created_at')->get();

        return response()->json([
            'success' => true,
            'data' => NotificationResource::collection($notifications),
        ], 200);
    }

    /**
     * 2. Hapus seluruh notifikasi dari Mobile App.
     */
    public function clearNotifications(): JsonResponse
    {
        Notification::query()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Semua notifikasi berhasil dihapus',
        ], 200);
    }

    /**
     * 3. Simpan log notifikasi dari Perangkat Hardware ESP32 / API Call.
     */
    public function storeLog(StoreNotificationRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $notification = Notification::create([
            'message' => $validated['message'] ?? 'Penyiraman selesai secara otomatis.',
        ]);

        return response()->json([
            'status' => 'success',
            'data' => new NotificationResource($notification),
        ], 201);
    }
}
