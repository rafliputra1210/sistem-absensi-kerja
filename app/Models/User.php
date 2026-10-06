<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'pin_gudang',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Relasi ke tabel attendances
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    // Relasi ke tabel leaves (izin/cuti)
    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }
}