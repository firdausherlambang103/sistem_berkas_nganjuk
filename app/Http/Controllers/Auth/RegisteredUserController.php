<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Jabatan;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $jabatans = Jabatan::where('is_admin', false)->orderBy('nama_jabatan')->get();
        return view('auth.register', compact('jabatans'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'jabatan_id' => ['required', 'exists:jabatans,id'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'jabatan_id' => $request->jabatan_id,
        ]);

        event(new Registered($user));

        // JADIKAN BARIS INI SEBAGAI KOMENTAR ATAU HAPUS
        // Auth::login($user);

        // Arahkan ke halaman login dengan pesan sukses
        return redirect(route('login'))->with('status', 'Pendaftaran berhasil! Akun Anda akan aktif setelah disetujui oleh Admin.');
    }
}

