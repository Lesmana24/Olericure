<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Setting;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'batas_suhu',
                'value' => '24'
            ],
            [
                'key' => 'batas_lembab',
                'value' => '60'
            ],
            [
                'key' => 'jadwal_hari', 
                'value' => '0,0,0,0,0,0,0'
            ],
            [
                'key' => 'jadwal_jam',  
                'value' => '07:00'
            ],
            [
                'key' => 'durasi_suhu',
                'value' => '4'
            ],
            [
                'key' => 'durasi_jadwal',
                'value' => '5'
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value']]
            );
        }
    }
}
