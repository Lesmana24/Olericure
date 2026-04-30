<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    // 1. Fungsi Menampilkan Dashboard
    public function index()
    {
        $batasSuhu = Setting::firstWhere('key', 'batas_suhu')->value ?? 24;
        $batasLembab = Setting::firstWhere('key', 'batas_lembab')->value ?? 60;
        $jadwalHari = Setting::query()->where('key', 'jadwal_hari')->value('value') ?? '0,0,0,0,0,0,0';
        $jadwalJam  = Setting::query()->where('key', 'jadwal_jam')->value('value') ?? '07:00';
        $durasiSuhu = Setting::query()->where('key', 'durasi_suhu')->value('value') ?? 3;
        $durasiJadwal = Setting::query()->where('key', 'durasi_jadwal')->value('value') ?? 5;

        $arrayHari = explode(',', $jadwalHari);

        return view('konten.home', [
            'title' => 'Dashboard IoT',
            'batasSuhu' => $batasSuhu,
            'batasLembab' => $batasLembab,
            'jadwalJam'   => $jadwalJam,
            'arrayHari'   => $arrayHari,
            'durasiSuhu' => $durasiSuhu,
            'durasiJadwal' => $durasiJadwal
        ]);
    }

    // 2. Fungsi Update Data
    public function updateSettings(Request $request)
    {
        $key = $request->input('key');
        $value = $request->input('value');

    
        Setting::query()->where('key', $key)->update(['value' => $value]);

        return response()->json(['status' => 'success']);
    }
}
