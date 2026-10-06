<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();

        $stats = [
            'hadir' => Attendance::where('date', $today)->where('status', 'hadir')->count(),
            'telat' => Attendance::where('date', $today)->where('status', 'telat')->count(),
            'izin'  => Attendance::where('date', $today)->where('status', 'izin')->count(),
            'alpha' => Attendance::where('date', $today)->where('status', 'alpha')->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}