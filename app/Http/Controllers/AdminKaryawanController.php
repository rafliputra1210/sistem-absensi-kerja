<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminKaryawanController extends Controller
{
    public function index()
    {
        $karyawans = User::where('role', '!=', 'admin')->latest()->get();
        return view('admin.karyawan.index', compact('karyawans'));
    }

    public function create()
    {
        return view('admin.karyawan.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|in:karyawan_kantor,supir,karyawan_gudang',
            'pin_gudang' => 'required_if:role,karyawan_gudang|nullable|digits:6|unique:users,pin_gudang',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'pin_gudang' => $request->role === 'karyawan_gudang' ? $request->pin_gudang : null,
        ]);

        return redirect()->route('admin.karyawan.index')->with('success', 'Data karyawan berhasil ditambahkan.');
    }

    public function edit(User $karyawan)
    {
        return view('admin.karyawan.form', compact('karyawan'));
    }

    public function update(Request $request, User $karyawan)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($karyawan->id)],
            'role' => 'required|in:karyawan_kantor,supir,karyawan_gudang',
            'pin_gudang' => [
                'required_if:role,karyawan_gudang',
                'nullable',
                'digits:6',
                Rule::unique('users', 'pin_gudang')->ignore($karyawan->id)
            ],
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'pin_gudang' => $request->role === 'karyawan_gudang' ? $request->pin_gudang : null,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $karyawan->update($data);

        return redirect()->route('admin.karyawan.index')->with('success', 'Data karyawan berhasil diperbarui.');
    }

    public function destroy(User $karyawan)
    {
        $karyawan->delete();
        return redirect()->route('admin.karyawan.index')->with('success', 'Data karyawan berhasil dihapus.');
    }
}
