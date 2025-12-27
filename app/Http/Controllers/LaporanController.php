<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Jabatan;
use App\Models\RiwayatBerkas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LaporanController extends Controller
{
    /**
     * Menampilkan halaman laporan statistik (Index).
     */
    public function index()
    {
        $jabatans = Jabatan::with([
            'users' => function ($query) {
                $query->withCount([
                    // 1. Total Masuk
                    'riwayatDiterima as total_masuk', 
                    
                    // 2. Total Keluar (Selesai)
                    'riwayatDikirim as total_keluar',

                    // 3. Sisa Berkas (Pending)
                    'berkasDiTangan as sisa_berkas' => function ($q) {
                        $q->whereIn('status', ['Diproses', 'Pending']);
                    },

                    // 4. Produktivitas Harian
                    'riwayatDikirim as produktivitas_harian' => function ($q) {
                        $q->whereDate('created_at', Carbon::today());
                    }
                ]);
            }
        ])
        // Urutkan: Kepala Kantor paling atas
        ->orderByRaw("CASE WHEN nama_jabatan = 'Kepala Kantor Pertanahan' THEN 0 ELSE 1 END ASC")
        ->orderBy('nama_jabatan', 'asc')
        ->get();
        
        return view('laporan.index', [
            'jabatans' => $jabatans,
        ]);
    }

    /**
     * Menampilkan rincian berkas user (Halaman Detail).
     */
    public function showBerkasByUser(User $user)
    {
        // 1. Ambil Berkas yang Sedang Diproses (Di Tangan User)
        $daftarBerkas = $user->berkasDiTangan()
            ->with('jenisPermohonan')
            ->latest()
            ->get();

        // 2. Ambil Riwayat Berkas yang Sudah Selesai (Dikirim oleh User)
        // Perlu relasi 'berkas' dan 'keUser' di model RiwayatBerkas
        $berkasKeluar = $user->riwayatDikirim()
            ->with(['berkas.jenisPermohonan', 'keUser.jabatan'])
            ->latest()
            ->get();

        // 3. Hitung Statistik Pelengkap
        $totalMasuk = $user->riwayatDiterima()->count();
        $totalKeluar = $berkasKeluar->count();
        $sisaBerkas = $daftarBerkas->count();

        // 4. Hitung Persentase Penyelesaian
        $totalBebanKerja = $totalKeluar + $sisaBerkas;
        $persentasePenyelesaian = $totalBebanKerja > 0 
            ? round(($totalKeluar / $totalBebanKerja) * 100, 1) 
            : 0;

        // 5. Logika Durasi (Waktu Mulai Argo)
        foreach ($daftarBerkas as $berkas) {
            // Butuh relasi 'riwayat' di Berkas dan 'dariUser' di RiwayatBerkas
            $riwayatPembayaran = $berkas->riwayat()
                ->whereHas('dariUser.jabatan', function ($query) {
                    $query->where('nama_jabatan', 'Petugas Loket Pembayaran');
                })
                ->orderBy('waktu_kirim', 'asc')
                ->first();
            
            $berkas->waktu_mulai_argo = $riwayatPembayaran ? $riwayatPembayaran->waktu_kirim : $berkas->created_at;
        }

        // 6. Kirim semua data ke View
        return view('laporan.show_berkas_by_user', [
            'petugas' => $user,
            'daftarBerkas' => $daftarBerkas,
            'berkasKeluar' => $berkasKeluar,
            'totalMasuk' => $totalMasuk,
            'totalKeluar' => $totalKeluar,
            'sisaBerkas' => $sisaBerkas,
            'persentasePenyelesaian' => $persentasePenyelesaian
        ]);
    }
}