<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Berkas;
use App\Models\User;
use App\Models\RiwayatBerkas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PerbaikanBerkasController extends Controller
{
    public function index(Request $request)
    {
        $berkas = null;
        $users = User::where('is_approved', true)->orderBy('name')->get(); // Ambil list user aktif

        if ($request->has('keyword') && $request->keyword != '') {
            // Cari berdasarkan Nomor Berkas atau Nama Pemohon
            $berkas = Berkas::with(['posisiSekarang', 'jenisPermohonan'])
                ->where('nomer_berkas', 'like', '%' . $request->keyword . '%')
                ->orWhere('nama_pemohon', 'like', '%' . $request->keyword . '%')
                ->first();
        }

        return view('admin.berkas.perbaikan', compact('berkas', 'users'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'target_user_id' => 'required|exists:users,id',
            'catatan' => 'required|string|max:255',
        ]);

        $berkas = Berkas::findOrFail($id);
        
        // Ambil nama user lama (sebelum dipindah)
        $oldUserName = $berkas->posisiSekarang ? $berkas->posisiSekarang->name : 'Sistem/Tidak Diketahui';
        
        $newUser = User::find($request->target_user_id);

        try {
            DB::transaction(function () use ($berkas, $request, $newUser, $oldUserName) {
                
                // 1. Catat di Riwayat Berkas SEBELUM update posisi
                // Kita catat bahwa Admin (Auth::id) memindahkan berkas ke Target User
                RiwayatBerkas::create([
                    'berkas_id' => $berkas->id,
                    'dari_user_id' => Auth::id(), // Admin yang melakukan aksi
                    'ke_user_id' => $newUser->id, // User tujuan
                    'status' => 'DIPERBAIKI ADMIN', // Status khusus penanda aksi admin
                    'keterangan' => "Admin memindahkan posisi berkas secara paksa dari [{$oldUserName}] ke [{$newUser->name}]. Alasan: {$request->catatan}",
                ]);

                // 2. Update Posisi Berkas
                $berkas->update([
                    'posisi_sekarang_user_id' => $newUser->id,
                    // Opsional: Reset status pengiriman agar user baru bisa melihat tombol aksi (terima/tolak)
                    // 'status_pengiriman' => 'pending', 
                ]);

            });

            return redirect()->route('admin.perbaikan.index', ['keyword' => $berkas->nomer_berkas])
                ->with('success', "Berkas berhasil dipindahkan ke {$newUser->name}.");

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }
}