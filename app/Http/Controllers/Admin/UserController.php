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
    public function index(Request $request)
    {
        $search = $request->input('search');

        // Query dasar untuk semua user dengan relasi jabatan
        $baseQuery = \App\Models\User::with('jabatan');

        // Logika Pencarian
        if ($search) {
            $baseQuery->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Ambil Data Pengguna Internal (Pegawai yang bukan Mitra, atau yang belum punya jabatan)
        $internalUsers = (clone $baseQuery)->where(function($q) {
            $q->whereHas('jabatan', function($q2) {
                $q2->where('is_mitra', false)->orWhereNull('is_mitra');
            })->orWhereNull('jabatan_id');
        })->orderBy('name')->get();

        // Ambil Data Mitra (Hanya yang jabatannya di-set sebagai Mitra)
        $mitraUsers = (clone $baseQuery)->whereHas('jabatan', function($q) {
            $q->where('is_mitra', true);
        })->orderBy('name')->get();

        return view('admin.users.index', compact('internalUsers', 'mitraUsers', 'search'));
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
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'jabatan_id' => 'required|exists:jabatans,id',
            'nomer_wa' => 'nullable|string|max:20',
            'akses_menu' => 'nullable|array',
            'akses_layer' => 'nullable|array',
            'is_approved' => 'required|boolean', // WAJIB ADA INI
        ]);

        // Jika password diisi, hash passwordnya. Jika kosong, jangan diupdate.
        if ($request->filled('password')) {
            $validated['password'] = bcrypt($request->password);
        } else {
            unset($validated['password']);
        }

        // Pastikan array dikonversi ke JSON agar masuk ke database (jika model tidak menggunakan casts)
        $validated['akses_menu'] = $request->has('akses_menu') ? json_encode($request->akses_menu) : json_encode([]);
        $validated['akses_layer'] = $request->has('akses_layer') ? json_encode($request->akses_layer) : json_encode([]);

        $user->update($validated);

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil diupdate.');
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