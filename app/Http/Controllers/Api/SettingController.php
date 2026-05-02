<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;

class SettingController extends Controller
{
    public function update(Request $request)
    {
        // 1. Validasi data dari Flutter
        $request->validate([
            'key' => 'required|string',
            'value' => 'required'
        ]);

        // 2. Simpan atau Update datanya
        // updateOrCreate itu magic function Laravel
        // Kalau 'key' udah ada, dia nge-update 'value'-nya. Kalau belum ada, dia bikin baris baru.
        Setting::updateOrCreate(
            ['key' => $request->key],
            ['value' => $request->value]
        );

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan ' . $request->key . ' berhasil disimpan!'
        ], 200);
    }
    public function getSettings()
    {
        // Pluck ini magic Laravel buat ngubah data tabel jadi array [ 'key' => 'value' ]
        $settings = Setting::pluck('value', 'key');
        
        return response()->json([
            'success' => true,
            'data' => $settings
        ], 200);
    }
}
