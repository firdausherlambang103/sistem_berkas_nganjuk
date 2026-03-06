<?php

namespace App\Http\Controllers\Mitra;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Jabatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use App\Http\Requests\Auth\LoginRequest;

class AuthController extends Controller
{
    // Menampilkan Halaman Login Khusus Mitra
    public function showLogin()
    {
        return view('mitra.auth.login');
    }

    // Proses Login Khusus Mitra
    public function storeLogin(LoginRequest $request)
    {
        $request->authenticate();
        $request->session()->regenerate();

        $user = Auth::user();

        // 1. Cek apakah akun sudah di-ACC (Disetujui) oleh Admin
        if (!$user->is_approved) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'Akun Anda sedang dalam proses peninjauan oleh Admin. Silakan tunggu persetujuan.',
            ]);
        }

        // 2. Pastikan yang login di sini HANYA Mitra (Berdasarkan is_mitra atau nama jabatan)
        if ($user->jabatan && ($user->jabatan->is_mitra || in_array($user->jabatan->nama_jabatan, ['PPAT', 'Freelance']))) {
            return redirect()->route('mitra.dashboard');
        }

        // Jika user internal (Admin/Pegawai) mencoba login lewat portal mitra, tolak
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return back()->withErrors([
            'email' => 'Halaman login ini khusus untuk Mitra (PPAT & Freelance).',
        ]);
    }

    // Menampilkan Halaman Register Khusus Mitra
    public function showRegister()
    {
        // HANYA ambil jabatan yang diset sebagai Mitra (atau secara hardcode 'PPAT', 'Freelance')
        $jabatans = Jabatan::where('is_mitra', true)
            ->orWhereIn('nama_jabatan', ['PPAT', 'Freelance'])
            ->get();
            
        return view('mitra.auth.register', compact('jabatans'));
    }

    // Proses Register Khusus Mitra
    public function storeRegister(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'nomer_wa' => ['required', 'string', 'max:20'], // [BARU] Validasi No WA
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
            'jabatan_id' => ['required', 'exists:jabatans,id'],
        ]);

        $jabatan = Jabatan::find($request->jabatan_id);
        if (!$jabatan->is_mitra && !in_array($jabatan->nama_jabatan, ['PPAT', 'Freelance'])) {
            return back()->withErrors(['jabatan_id' => 'Jabatan tidak valid untuk pendaftaran ini.']);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'nomer_wa' => $request->nomer_wa, // [BARU] Simpan No WA
            'password' => Hash::make($request->password),
            'jabatan_id' => $request->jabatan_id,
            'is_approved' => false,
        ]);

        event(new Registered($user));

        return redirect()->route('mitra.login')->with('status', 'Pendaftaran berhasil! Akun Anda sedang menunggu persetujuan Admin.');
    }
}