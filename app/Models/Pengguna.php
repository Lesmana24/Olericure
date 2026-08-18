<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Pengguna extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Nama tabel database yang digunakan oleh model ini.
     *
     * @var string
     */
    protected $table = 'pengguna';

    /**
     * Atribut yang dapat diisi secara mass-assignment.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama',
        'password',
    ];

    /**
     * Atribut yang disembunyikan dari serialisasi JSON.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /**
     * Relasi HasMany ke riwayat scan kesehatan tanaman.
     */
    public function plantScans()
    {
        return $this->hasMany(PlantHealthScan::class, 'user_id');
    }
}

