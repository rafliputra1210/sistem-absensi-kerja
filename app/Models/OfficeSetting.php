<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfficeSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'office_ip',
        'office_lat',
        'office_lng',
        'radius_meter',
        'start_time',
        'end_time',
        'min_overtime_minutes',
    ];
}