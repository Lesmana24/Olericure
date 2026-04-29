<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    // 1. Fungsi Menampilkan Dashboard
    public function index()
    {
        $batasSuhu = Setting::where('key', 'batas_suhu')->first()->value ?? 24;
        $batasLembab = Setting::where('key', 'batas_lembab')->first()->value ?? 60;
        $jadwalHari = Setting::where('key', 'jadwal_hari')->value('value') ?? '0,0,0,0,0,0,0';
        $jadwalJam  = Setting::where('key', 'jadwal_jam')->value('value') ?? '07:00';
        $durasiSuhu = Setting::where('key', 'durasi_suhu')->value('value') ?? 3;
        $durasiJadwal = Setting::where('key', 'durasi_jadwal')->value('value') ?? 5;

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

    
        Setting::where('key', $key)->update(['value' => $value]);

        return response()->json(['status' => 'success']);
    }
}
