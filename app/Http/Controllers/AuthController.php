<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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

    // Proses login menggunakan nama
    public function login(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string'],
            'password' => ['required'],
        ]);

        $loginInput = trim($request->input('name'));
        $password = $request->input('password');

        // Coba autentikasi menggunakan nama (atau email sebagai alternatif)
        if (Auth::attempt(['name' => $loginInput, 'password' => $password], $request->boolean('remember')) ||
            Auth::attempt(['email' => $loginInput, 'password' => $password], $request->boolean('remember'))) {
            $request->session()->regenerate();

            return $this->redirectUserBasedOnRole();
        }

        // Coba pencarian fleksibel (case-insensitive atau prefix nama seperti "Budi" untuk "Budi (Supir)")
        $user = User::whereRaw('LOWER(name) = ?', [strtolower($loginInput)])
            ->orWhereRaw('LOWER(email) = ?', [strtolower($loginInput)])
            ->first();

        if (!$user) {
            $user = User::where('name', 'like', $loginInput . ' (%')->first();
        }

        if ($user && Hash::check($password, $user->password)) {
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            return $this->redirectUserBasedOnRole();
        }

        return back()->with('error', 'Nama atau password salah. Silakan coba lagi.')->withInput($request->only('name'));
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
            case 'karyawan_gudang':
                return redirect()->route('gudang.dashboard');
            default:
                Auth::logout();
                return redirect('/login')->with('error', 'Role pengguna tidak valid.');
        }
    }
}