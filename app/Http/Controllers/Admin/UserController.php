<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Jabatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    /**
     * Menampilkan daftar semua pengguna (disetujui dan menunggu).
     */
    public function index()
    {
        // Mengambil pengguna yang belum disetujui
        $pendingUsers = User::where('is_approved', false)
                            ->orderBy('created_at', 'desc')
                            ->get();
        
        // (PERBAIKAN) Mengambil pengguna yang SUDAH disetujui
        $approvedUsers = User::where('is_approved', true)
                             ->with('jabatan') // Memuat relasi jabatan untuk efisiensi
                             ->orderBy('name', 'asc')
                             ->get();
                                
        // Kirim KEDUA variabel ke view
        return view('admin.users.index', compact('pendingUsers', 'approvedUsers'));
    }

    /**
     * Menyetujui pendaftaran pengguna baru.
     */
    public function approve(User $user)
    {
        $user->is_approved = true;
        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'User ' . $user->name . ' telah disetujui.');
    }

    /**
     * Menampilkan form untuk mengedit data pengguna.
     */
    public function edit(User $user)
    {
        $jabatans = Jabatan::orderBy('nama_jabatan')->get();
        return view('admin.users.edit', compact('user', 'jabatans'));
    }

    /**
     * Memproses pembaruan data pengguna.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'jabatan_id' => ['required', 'exists:jabatans,id'],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->jabatan_id = $request->jabatan_id;

        // Hanya update password jika diisi
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'Data user ' . $user->name . ' berhasil diperbarui.');
    }

    /**
     * Menghapus data pengguna.
     */
    public function destroy(User $user)
    {
        // Pengaman agar admin tidak bisa menghapus diri sendiri
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $userName = $user->name;
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User ' . $userName . ' berhasil dihapus.');
    }
}

