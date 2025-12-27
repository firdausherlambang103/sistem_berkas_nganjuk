<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Jabatan;
use App\Models\RiwayatBerkas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    /**
     * Menampilkan halaman laporan statistik (Index).
     */
    public function index()
    {
        $jabatans = Jabatan::with([
            'users' => function ($query) {
                // Ambil data user beserta hitungan berkasnya
                $query->withCount([
                    // Menghitung jumlah record di tabel riwayat_berkas dimana ke_user_id = user ini
                    'riwayatDiterima as total_masuk', 
                    
                    // Menghitung jumlah record di tabel riwayat_berkas dimana dari_user_id = user ini
                    'riwayatDikirim as total_keluar',

                    // Hitung sisa berkas yang masih dipegang (status Diproses/Pending)
                    'berkasDiTangan as sisa_berkas' => function ($q) {
                        $q->whereIn('status', ['Diproses', 'Pending']);
                    }
                ]);
            }
        ])
        ->orderByRaw("CASE WHEN nama_jabatan = 'Kepala Kantor Pertanahan' THEN 0 ELSE 1 END ASC")
        ->orderBy('nama_jabatan', 'asc')
        ->get();
        
        return view('laporan.index', [
            'jabatans' => $jabatans,
        ]);
    }

    /**
     * Menampilkan rincian berkas user.
     * Perbaikan: Mengirimkan variabel $daftarBerkas yang dibutuhkan oleh view.
     */
    public function showBerkasByUser(User $user)
    {
        // Mengambil berkas yang sedang dipegang user
        $daftarBerkas = $user->berkasDiTangan()->with('jenisPermohonan')->latest()->get();

        // Logika menghitung waktu mulai argo
        foreach ($daftarBerkas as $berkas) {
            $riwayatPembayaran = $berkas->riwayat()
                ->whereHas('dariUser.jabatan', function ($query) {
                    $query->where('nama_jabatan', 'Petugas Loket Pembayaran');
                })
                ->orderBy('waktu_kirim', 'asc')
                ->first();
            
            // Jika ada riwayat dari loket pembayaran, gunakan waktu kirimnya.
            // Jika tidak, gunakan waktu pembuatan berkas.
            $berkas->waktu_mulai_argo = $riwayatPembayaran ? $riwayatPembayaran->waktu_kirim : $berkas->created_at;
        }

        // Kirim variabel $daftarBerkas yang dibutuhkan oleh View
        return view('laporan.show_berkas_by_user', [
            'petugas' => $user,
            'daftarBerkas' => $daftarBerkas 
        ]);
    }
}