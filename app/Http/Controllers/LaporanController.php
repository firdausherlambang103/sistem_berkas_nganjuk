<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Jabatan;
use App\Models\Berkas;
use Illuminate\Http\Request;
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
                    // 1. Total Masuk (Difilter Tahun Berkas & Kecualikan 'Ditutup')
                    'riwayatDiterima as total_masuk' => function ($q) use ($tahun) {
                        $q->whereHas('berkas', function ($b) use ($tahun) {
                            $b->where('tahun', $tahun)
                              ->where('status', '!=', 'Ditutup');
                        });
                    },
                    
                    // 2. Total Keluar (Selesai) (Difilter Tahun Berkas & Kecualikan 'Ditutup')
                    'riwayatDikirim as total_keluar' => function ($q) use ($tahun) {
                        $q->whereHas('berkas', function ($b) use ($tahun) {
                            $b->where('tahun', $tahun)
                              ->where('status', '!=', 'Ditutup');
                        });
                    },

                    // 3. [BARU] Berkas Sedang Diproses (Status: Diproses)
                    'berkasDiTangan as berkas_proses' => function ($q) use ($tahun) {
                        $q->where('status', 'Diproses')
                          ->where('tahun', $tahun);
                    },

                    // 4. [BARU] Berkas Pending/Ditunda (Status: Pending)
                    'berkasDiTangan as berkas_pending' => function ($q) use ($tahun) {
                        $q->where('status', 'Pending')
                          ->where('tahun', $tahun);
                    },

                    // 5. [BARU] Berkas Jatuh Tempo
                    // Logic: Status aktif (Diproses/Pending) DAN Waktu Sekarang > (Waktu Mulai + Timeline)
                    'berkasDiTangan as berkas_jatuh_tempo' => function ($q) use ($tahun) {
                        $q->whereIn('status', ['Diproses', 'Pending'])
                          ->where('tahun', $tahun)
                          ->whereHas('jenisPermohonan', function ($jp) {
                              $jp->whereRaw('DATE_ADD(berkas.waktu_mulai_proses, INTERVAL jenis_permohonans.waktu_timeline_hari DAY) < NOW()');
                          });
                    },

                    // 6. Produktivitas Harian (Tetap Harian)
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
            ->orderBy('urutan', 'asc') 
            ->orderBy('nama_jabatan', 'asc')
            ->get();
        
        // 6. Data Tabular (Daftar Semua Berkas Filter Tahun)
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
            'tahun' => $tahun, 
            'data' => $data    
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
                    // 1. Total Masuk
                    'riwayatDiterima as total_masuk' => function ($q) use ($tahun) {
                        $q->whereHas('berkas', function ($b) use ($tahun) {
                            $b->where('tahun', $tahun)
                              ->where('status', '!=', 'Ditutup');
                        });
                    },
                    // 2. Total Keluar
                    'riwayatDikirim as total_keluar' => function ($q) use ($tahun) {
                        $q->whereHas('berkas', function ($b) use ($tahun) {
                            $b->where('tahun', $tahun)
                              ->where('status', '!=', 'Ditutup');
                        });
                    },
                    // 3. Sisa Berkas (Masih pakai 'Sisa' untuk monitor global, atau bisa dipecah jika perlu)
                    'berkasDiTangan as sisa_berkas' => function ($q) use ($tahun) {
                        $q->whereIn('status', ['Diproses', 'Pending'])
                          ->where('tahun', $tahun);
                    },
                    // 4. Produktivitas Harian
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
            ->orderBy('urutan', 'asc')
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

        // 1. Ambil Berkas yang Sedang Diproses (Di Tangan User)
        $daftarBerkas = $user->berkasDiTangan()
            ->where('tahun', $tahun)
            ->with('jenisPermohonan')
            ->latest()
            ->get();

        // 2. Ambil Riwayat Berkas yang Sudah Selesai (Dikirim oleh User)
        $berkasKeluar = $user->riwayatDikirim()
            ->whereHas('berkas', function ($q) use ($tahun) {
                $q->where('tahun', $tahun)
                  ->where('status', '!=', 'Ditutup');
            })
            ->with(['berkas.jenisPermohonan', 'keUser.jabatan'])
            ->latest()
            ->get();

        // 3. Hitung Statistik Pelengkap
        $totalMasuk = $user->riwayatDiterima()
            ->whereHas('berkas', function ($q) use ($tahun) {
                $q->where('tahun', $tahun)
                  ->where('status', '!=', 'Ditutup');
            })->count();
            
        $totalKeluar = $berkasKeluar->count();
        $sisaBerkas = $daftarBerkas->count();

        // 4. Hitung Persentase Penyelesaian
        $totalBebanKerja = $totalKeluar + $sisaBerkas;
        $persentasePenyelesaian = $totalBebanKerja > 0 
            ? round(($totalKeluar / $totalBebanKerja) * 100, 1) 
            : 0;

        // 5. Logika Durasi
        foreach ($daftarBerkas as $berkas) {
            $riwayatPembayaran = $berkas->riwayat()
                ->whereHas('dariUser.jabatan', function ($query) {
                    $query->where('nama_jabatan', 'Petugas Loket Pembayaran');
                })
                ->orderBy('waktu_kirim', 'asc')
                ->first();
            
            $berkas->waktu_mulai_argo = $riwayatPembayaran ? $riwayatPembayaran->waktu_kirim : $berkas->created_at;
        }

        return view('laporan.show_berkas_by_user', [
            'petugas' => $user,
            'daftarBerkas' => $daftarBerkas,
            'berkasKeluar' => $berkasKeluar,
            'totalMasuk' => $totalMasuk,
            'totalKeluar' => $totalKeluar,
            'sisaBerkas' => $sisaBerkas,
            'persentasePenyelesaian' => $persentasePenyelesaian,
            'tahun' => $tahun 
        ]);
    }
}