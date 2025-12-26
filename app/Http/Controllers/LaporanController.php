<?php

namespace App\Http\Controllers;

use App\Models\RiwayatBerkas;
use App\Models\User;
use App\Models\Jabatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class LaporanController extends Controller
{
    /**
     * Menampilkan halaman laporan statistik.
     * Logika telah diubah untuk mengelompokkan user per jabatan.
     */
    public function index()
    {
        $jabatans = Jabatan::with([
            'users' => function ($query) {
                $query->whereHas('berkasDiTangan', function ($subQuery) {
                    $subQuery->whereIn('status', ['Diproses', 'Pending']);
                })
                ->withCount(['berkasDiTangan' => function ($subQuery) {
                    $subQuery->whereIn('status', ['Diproses', 'Pending']);
                }]);
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
     * Menampilkan rincian berkas yang dipegang oleh seorang user.
     * Fungsi ini sekarang mengambil data waktu mulai "argo" dari riwayat berkas.
     */
    public function showBerkasByUser(User $user)
    {
        // 1. Berkas MASUK (Dimana user ini adalah PENERIMA / ke_user_id)
        // Kita load 'dariUser' untuk tahu siapa pengirimnya
        $berkasMasuk = RiwayatBerkas::with(['berkas.jenisPermohonan', 'dariUser'])
            ->where('ke_user_id', $user->id)
            ->latest()
            ->get();

        // 2. Berkas KELUAR (Dimana user ini adalah PENGIRIM / dari_user_id)
        // Kita load 'keUser' untuk tahu dikirim ke siapa selanjutnya
        $berkasKeluar = RiwayatBerkas::with(['berkas.jenisPermohonan', 'keUser'])
            ->where('dari_user_id', $user->id)
            ->latest()
            ->get();

        // 3. Hitung Statistik Performa
        $totalMasuk = $berkasMasuk->count();
        $totalKeluar = $berkasKeluar->count();
        
        // Hitung persentase produktivitas (Keluar dibagi Masuk)
        $persentasePenyelesaian = 0;
        if($totalMasuk > 0){
             $persentasePenyelesaian = round(($totalKeluar / $totalMasuk) * 100, 1);
        }

        // Return ke view yang sama dengan data yang lebih lengkap
        return view('laporan.show_berkas_by_user', compact(
            'user', 
            'berkasMasuk', 
            'berkasKeluar', 
            'totalMasuk', 
            'totalKeluar',
            'persentasePenyelesaian'
        ));
    }
}

