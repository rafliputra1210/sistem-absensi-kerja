<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Menampilkan halaman login
    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectUserBasedOnRole();
        }
        return view('auth.login');
    }

    // Proses login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return $this->redirectUserBasedOnRole();
        }

        return back()->with('error', 'Email atau password salah. Silakan coba lagi.');
    }

    // Proses Logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    // Fungsi bantuan untuk mengarahkan rute sesuai role
    private function redirectUserBasedOnRole()
    {
        $role = Auth::user()->role;

        switch ($role) {
            case 'admin':
                return redirect()->route('admin.dashboard');
            case 'karyawan_kantor':
                return redirect()->route('kantor.dashboard');
            case 'supir':
                return redirect()->route('supir.dashboard');
            default:
                Auth::logout();
                return redirect('/login')->with('error', 'Role tidak valid untuk akses login ini (Gudang gunakan Kiosk).');
        }
    }
}