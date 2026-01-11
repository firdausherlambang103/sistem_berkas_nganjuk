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
     * Menampilkan halaman laporan statistik (Index) dengan Filter Seksi.
     */
    public function index(Request $request)
    {
        // 1. Ambil daftar Seksi unik untuk filter dropdown (abaikan yang null)
        $listSeksi = Jabatan::select('seksi')
            ->whereNotNull('seksi')
            ->distinct()
            ->pluck('seksi');

        // 2. Mulai Query Jabatan dengan Eager Loading
        $query = Jabatan::with([
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
        ]);

        // 3. Terapkan Filter jika user memilih 'seksi'
        if ($request->filled('seksi')) {
            $query->where('seksi', $request->input('seksi'));
        }

        // 4. Eksekusi Query dengan Sorting
        $jabatans = $query
            // Urutkan: Kepala Kantor paling atas
            ->orderByRaw("CASE WHEN nama_jabatan = 'Kepala Kantor Pertanahan' THEN 0 ELSE 1 END ASC")
            ->orderBy('nama_jabatan', 'asc')
            ->get();
        
        return view('laporan.index', [
            'jabatans' => $jabatans,
            'listSeksi' => $listSeksi,              // Data untuk dropdown
            'currentSeksi' => $request->input('seksi'), // Agar dropdown tetap terpilih setelah submit
        ]);
    }

    /**
     * Menampilkan halaman dashboard khusus monitor (Full Screen).
     * Logika query mirip dengan index, namun return view ke 'laporan.monitor'
     */
    public function monitor(Request $request)
    {
        // 1. Ambil daftar Seksi unik
        $listSeksi = Jabatan::select('seksi')
            ->whereNotNull('seksi')
            ->distinct()
            ->pluck('seksi');

        // 2. Query Jabatan dengan Eager Loading
        $query = Jabatan::with([
            'users' => function ($query) {
                $query->withCount([
                    'riwayatDiterima as total_masuk', 
                    'riwayatDikirim as total_keluar',
                    'berkasDiTangan as sisa_berkas' => function ($q) {
                        $q->whereIn('status', ['Diproses', 'Pending']);
                    },
                    'riwayatDikirim as produktivitas_harian' => function ($q) {
                        $q->whereDate('created_at', Carbon::today());
                    }
                ]);
            }
        ]);

        // 3. Filter Seksi
        if ($request->filled('seksi')) {
            $query->where('seksi', $request->input('seksi'));
        }

        // 4. Sorting
        $jabatans = $query
            ->orderByRaw("CASE WHEN nama_jabatan = 'Kepala Kantor Pertanahan' THEN 0 ELSE 1 END ASC")
            ->orderBy('nama_jabatan', 'asc')
            ->get();
        
        return view('laporan.monitor', [
            'jabatans' => $jabatans,
            'listSeksi' => $listSeksi,
            'currentSeksi' => $request->input('seksi'),
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