<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pengguna;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // === FUNGSI REGISTER MOBILE ===
    public function register(Request $request)
    {
        // 1. Validasi input
        $request->validate([
            'nama' => 'required|unique:pengguna,nama',
            'password' => 'required|min:4',
        ]);

        // 2. Simpan ke database
        $user = Pengguna::create([
            'nama' => $request->nama,
            'password' => Hash::make($request->password), // Password WAJIB di-hash
        ]);

        // 3. Langsung cetak token biar habis daftar otomatis login
        $token = $user->createToken('flutter-mobile-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Berhasil Daftar Akun',
            'token' => $token,
            'user' => $user
        ], 201);
    }

    // === FUNGSI LOGIN MOBILE ===
    public function login(Request $request)
    {
        // 1. Validasi input (Ganti email jadi nama)
        $request->validate([
            'nama' => 'required',
            'password' => 'required',
        ]);

        // 2. Cari data pengguna berdasarkan 'nama'
        $user = Pengguna::firstwhere('nama', $request->nama)->first();

        // 3. Cek apakah user ada dan passwordnya cocok
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Nama atau Password salah bang!'
            ], 401);
        }

        // 4. Cetak Token Sanctum buat Flutter
        $token = $user->createToken('flutter-mobile-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login Berhasil',
            'token' => $token,
            'user' => $user
        ], 200);
    }

    // === FUNGSI LOGOUT MOBILE ===
    public function logout(Request $request)
    {
        // Hapus token yang lagi dipakai saat ini
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil Log Out'
        ], 200);
    }
}
