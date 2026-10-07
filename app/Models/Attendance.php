<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'status',
        'ip_address_in',
        'lat_in',
        'lng_in',
        'photo_in',
        'lat_out',
        'lng_out',
        'photo_out',
        'notes',
        'is_overtime',
        'overtime_minutes',
        'overtime_reason',
        'overtime_status'
    ];

    // Relasi balik ke User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}