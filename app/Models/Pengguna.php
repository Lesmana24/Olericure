<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class Pengguna extends Authenticatable
{
    use HasApiTokens, Notifiable;
    protected $table = 'pengguna';

    protected $fillable = [
        'nama',
        'password',
    ];
    protected $hidden = [
        'password',
    ];
}
