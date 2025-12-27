<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Jabatan;
use App\Models\RiwayatBerkas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon; // Import Carbon untuk menangani tanggal/waktu

class LaporanController extends Controller
{
    /**
     * Menampilkan halaman laporan statistik (Index).
     * Termasuk produktivitas harian yang otomatis reset setiap hari.
     */
    public function index()
    {
        $jabatans = Jabatan::with([
            'users' => function ($query) {
                // Ambil data user beserta hitungan statistiknya
                $query->withCount([
                    // 1. Total Masuk: Semua riwayat yang diterima user ini
                    'riwayatDiterima as total_masuk', 
                    
                    // 2. Total Keluar (Selesai): Semua riwayat yang dikirim/diselesaikan user ini
                    'riwayatDikirim as total_keluar',

                    // 3. Sisa Berkas (Pending): Berkas aktif yang sedang dipegang
                    // Pastikan di Model User relasi berkasDiTangan sudah memfilter status 'Ditutup'
                    'berkasDiTangan as sisa_berkas' => function ($q) {
                        $q->whereIn('status', ['Diproses', 'Pending']);
                    },

                    // 4. PRODUKTIVITAS HARIAN (Reset otomatis jam 00:00)
                    // Menghitung jumlah berkas yang diselesaikan HARI INI
                    'riwayatDikirim as produktivitas_harian' => function ($q) {
                        $q->whereDate('created_at', Carbon::today());
                    }
                ]);
            }
        ])
        // Urutkan: Kepala Kantor paling atas, sisanya urut abjad
        ->orderByRaw("CASE WHEN nama_jabatan = 'Kepala Kantor Pertanahan' THEN 0 ELSE 1 END ASC")
        ->orderBy('nama_jabatan', 'asc')
        ->get();
        
        return view('laporan.index', [
            'jabatans' => $jabatans,
        ]);
    }

    /**
     * Menampilkan rincian berkas user (Halaman Detail).
     * Mengirimkan variabel lengkap untuk mencegah error "Undefined variable" di View.
     */
    public function showBerkasByUser(User $user)
    {
        // 1. Ambil Berkas yang Sedang Diproses (Di Tangan User)
        $daftarBerkas = $user->berkasDiTangan()
            ->with('jenisPermohonan')
            ->latest()
            ->get();

        // 2. Ambil Riwayat Berkas yang Sudah Selesai (Dikirim oleh User)
        // Diambil juga untuk keperluan statistik meskipun tidak ditampilkan di tabel (sesuai view lama)
        $berkasKeluar = $user->riwayatDikirim()
            ->with(['berkas.jenisPermohonan', 'keUser.jabatan'])
            ->latest()
            ->get();

        // 3. Hitung Statistik Pelengkap
        // Variabel ini dihitung agar jika View membutuhkannya di masa depan, tidak error
        $totalMasuk = $user->riwayatDiterima()->count();
        $totalKeluar = $berkasKeluar->count();
        $sisaBerkas = $daftarBerkas->count();

        // 4. Hitung Persentase Penyelesaian
        $totalBebanKerja = $totalKeluar + $sisaBerkas;
        $persentasePenyelesaian = $totalBebanKerja > 0 
            ? round(($totalKeluar / $totalBebanKerja) * 100, 1) 
            : 0;

        // 5. Logika Durasi (Waktu Mulai Argo)
        // Menentukan kapan "jam argometer" berkas dimulai untuk user ini
        foreach ($daftarBerkas as $berkas) {
            // Cari riwayat kapan berkas ini dikirim oleh Petugas Loket
            $riwayatPembayaran = $berkas->riwayat()
                ->whereHas('dariUser.jabatan', function ($query) {
                    $query->where('nama_jabatan', 'Petugas Loket Pembayaran');
                })
                ->orderBy('waktu_kirim', 'asc')
                ->first();
            
            // Jika ada data pembayaran, gunakan waktu itu. Jika tidak, gunakan waktu buat.
            $berkas->waktu_mulai_argo = $riwayatPembayaran ? $riwayatPembayaran->waktu_kirim : $berkas->created_at;
        }

        // 6. Kirim semua data ke View 'laporan.show_berkas_by_user'
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