<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Jabatan;
use App\Models\RiwayatBerkas;
use App\Models\Berkas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LaporanController extends Controller
{
    /**
     * Menampilkan halaman laporan statistik (Index) dengan Filter Seksi dan Tahun.
     */
    public function index(Request $request)
    {
        // 1. Ambil Tahun dari Request (Default: Tahun Sekarang)
        $tahun = $request->input('tahun', date('Y'));

        // 2. Ambil daftar Seksi unik untuk filter dropdown
        $listSeksi = Jabatan::select('seksi')
            ->whereNotNull('seksi')
            ->distinct()
            ->pluck('seksi');

        // 3. Mulai Query Jabatan dengan Eager Loading + Filter Tahun
        $query = Jabatan::with([
            'users' => function ($query) use ($tahun) {
                $query->withCount([
                    // 1. Total Masuk (Difilter Tahun Berkas)
                    'riwayatDiterima as total_masuk' => function ($q) use ($tahun) {
                        $q->whereHas('berkas', function ($b) use ($tahun) {
                            $b->where('tahun', $tahun);
                        });
                    },
                    
                    // 2. Total Keluar (Selesai) (Difilter Tahun Berkas)
                    'riwayatDikirim as total_keluar' => function ($q) use ($tahun) {
                        $q->whereHas('berkas', function ($b) use ($tahun) {
                            $b->where('tahun', $tahun);
                        });
                    },

                    // 3. Sisa Berkas (Pending) (Difilter Tahun Berkas)
                    'berkasDiTangan as sisa_berkas' => function ($q) use ($tahun) {
                        $q->whereIn('status', ['Diproses', 'Pending'])
                          ->where('tahun', $tahun);
                    },

                    // 4. Produktivitas Harian (Tetap Harian, tidak perlu filter tahun kecuali mau detail)
                    'riwayatDikirim as produktivitas_harian' => function ($q) {
                        $q->whereDate('created_at', Carbon::today());
                    }
                ]);
            }
        ]);

        // 4. Terapkan Filter Seksi
        if ($request->filled('seksi')) {
            $query->where('seksi', $request->input('seksi'));
        }

        // 5. Eksekusi Query dengan Sorting
        $jabatans = $query
            ->orderByRaw("CASE WHEN nama_jabatan = 'Kepala Kantor Pertanahan' THEN 0 ELSE 1 END ASC")
            ->orderBy('nama_jabatan', 'asc')
            ->get();
        
        // 6. Data Tabular (Daftar Semua Berkas Filter Tahun)
        // Ini tambahan agar tampilan tabel di view index berfungsi
        $data = Berkas::with(['jenisPermohonan', 'posisiSekarang.jabatan'])
                      ->where('tahun', $tahun);

        if ($request->filled('search')) {
            $search = $request->search;
            $data->where(function($q) use ($search) {
                $q->where('nomer_berkas', 'like', "%{$search}%")
                  ->orWhere('nama_pemohon', 'like', "%{$search}%")
                  ->orWhere('nomer_hak', 'like', "%{$search}%")
                  ->orWhere('desa', 'like', "%{$search}%");
            });
        }

        $data = $data->orderBy('created_at', 'desc')
                     ->paginate(20)
                     ->withQueryString();

        return view('laporan.index', [
            'jabatans' => $jabatans,
            'listSeksi' => $listSeksi,
            'currentSeksi' => $request->input('seksi'),
            'tahun' => $tahun, // Kirim variabel tahun ke view
            'data' => $data    // Kirim data tabular ke view
        ]);
    }

    /**
     * Menampilkan halaman dashboard khusus monitor (Full Screen).
     */
    public function monitor(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));

        $listSeksi = Jabatan::select('seksi')
            ->whereNotNull('seksi')
            ->distinct()
            ->pluck('seksi');

        $query = Jabatan::with([
            'users' => function ($query) use ($tahun) {
                $query->withCount([
                    'riwayatDiterima as total_masuk' => function ($q) use ($tahun) {
                        $q->whereHas('berkas', function ($b) use ($tahun) {
                            $b->where('tahun', $tahun);
                        });
                    },
                    'riwayatDikirim as total_keluar' => function ($q) use ($tahun) {
                        $q->whereHas('berkas', function ($b) use ($tahun) {
                            $b->where('tahun', $tahun);
                        });
                    },
                    'berkasDiTangan as sisa_berkas' => function ($q) use ($tahun) {
                        $q->whereIn('status', ['Diproses', 'Pending'])
                          ->where('tahun', $tahun);
                    },
                    'riwayatDikirim as produktivitas_harian' => function ($q) {
                        $q->whereDate('created_at', Carbon::today());
                    }
                ]);
            }
        ]);

        if ($request->filled('seksi')) {
            $query->where('seksi', $request->input('seksi'));
        }

        $jabatans = $query
            ->orderByRaw("CASE WHEN nama_jabatan = 'Kepala Kantor Pertanahan' THEN 0 ELSE 1 END ASC")
            ->orderBy('nama_jabatan', 'asc')
            ->get();
        
        return view('laporan.monitor', [
            'jabatans' => $jabatans,
            'listSeksi' => $listSeksi,
            'currentSeksi' => $request->input('seksi'),
            'tahun' => $tahun
        ]);
    }

    /**
     * Menampilkan rincian berkas user (Halaman Detail).
     */
    public function showBerkasByUser(Request $request, User $user)
    {
        $tahun = $request->input('tahun', date('Y'));

        // 1. Ambil Berkas yang Sedang Diproses (Di Tangan User) - Filter Tahun
        $daftarBerkas = $user->berkasDiTangan()
            ->where('tahun', $tahun)
            ->with('jenisPermohonan')
            ->latest()
            ->get();

        // 2. Ambil Riwayat Berkas yang Sudah Selesai (Dikirim oleh User) - Filter Tahun
        $berkasKeluar = $user->riwayatDikirim()
            ->whereHas('berkas', function ($q) use ($tahun) {
                $q->where('tahun', $tahun);
            })
            ->with(['berkas.jenisPermohonan', 'keUser.jabatan'])
            ->latest()
            ->get();

        // 3. Hitung Statistik Pelengkap
        $totalMasuk = $user->riwayatDiterima()
            ->whereHas('berkas', function ($q) use ($tahun) {
                $q->where('tahun', $tahun);
            })->count();
            
        $totalKeluar = $berkasKeluar->count();
        $sisaBerkas = $daftarBerkas->count();

        // 4. Hitung Persentase Penyelesaian
        $totalBebanKerja = $totalKeluar + $sisaBerkas;
        $persentasePenyelesaian = $totalBebanKerja > 0 
            ? round(($totalKeluar / $totalBebanKerja) * 100, 1) 
            : 0;

        // 5. Logika Durasi (Waktu Mulai Argo)
        foreach ($daftarBerkas as $berkas) {
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
            'persentasePenyelesaian' => $persentasePenyelesaian,
            'tahun' => $tahun // Kirim tahun agar bisa difilter di view detail juga jika perlu
        ]);
    }
}