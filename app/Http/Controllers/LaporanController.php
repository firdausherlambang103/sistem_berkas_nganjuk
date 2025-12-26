<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Jabatan;
use App\Models\RiwayatBerkas; // Pastikan Model ini di-import
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
     * Menampilkan rincian berkas (Masuk & Keluar) user beserta performanya.
     */
    public function showBerkasByUser(User $user)
    {
        // 1. DATA BERKAS MASUK 
        // (User ini sebagai penerima/ke_user_id). Kita load 'dariUser' untuk tahu pengirimnya.
        $berkasMasuk = RiwayatBerkas::with(['berkas.jenisPermohonan', 'dariUser'])
            ->where('ke_user_id', $user->id)
            ->latest()
            ->get();

        // 2. DATA BERKAS KELUAR 
        // (User ini sebagai pengirim/dari_user_id). Kita load 'keUser' untuk tahu tujuannya.
        $berkasKeluar = RiwayatBerkas::with(['berkas.jenisPermohonan', 'keUser'])
            ->where('dari_user_id', $user->id)
            ->latest()
            ->get();

        // 3. HITUNG STATISTIK PERFORMA
        $totalMasuk = $berkasMasuk->count();
        $totalKeluar = $berkasKeluar->count();
        
        // Rasio Produktivitas: (Keluar / Masuk) * 100
        $persentasePenyelesaian = 0;
        if($totalMasuk > 0){
             $persentasePenyelesaian = round(($totalKeluar / $totalMasuk) * 100, 1);
        }

        // Return ke View
        return view('laporan.show_berkas_by_user', [
            'petugas' => $user, // Tetap gunakan variabel 'petugas' agar kompatibel
            'user' => $user,    // Tambahkan alias 'user' untuk fleksibilitas
            'berkasMasuk' => $berkasMasuk,
            'berkasKeluar' => $berkasKeluar,
            'totalMasuk' => $totalMasuk,
            'totalKeluar' => $totalKeluar,
            'persentasePenyelesaian' => $persentasePenyelesaian
        ]);
    }
}