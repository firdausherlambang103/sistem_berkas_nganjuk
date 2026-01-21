<?php

namespace App\Http\Controllers;

use App\Models\Berkas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Menampilkan halaman dashboard utama.
     */
    public function index(Request $request): View
    {
        // 1. Ambil Tahun dari Request (Default: Tahun Sekarang)
        $tahun = $request->input('tahun', date('Y'));

        // 2. Query Dasar (Start point untuk semua hitungan agar sinkron)
        // Kita menggunakan query builder awal agar filter tahun konsisten di semua hitungan
        $baseQuery = Berkas::query();

        if ($tahun != 'Semua') {
            $baseQuery->where('tahun', $tahun);
        }

        // 3. Hitung Statistik (Clone baseQuery agar filter tahun terbawa)
        $totalBerkas   = (clone $baseQuery)->count();
        $totalDiproses = (clone $baseQuery)->where('status', 'Diproses')->count();
        $totalSelesai  = (clone $baseQuery)->where('status', 'Selesai')->count();
        
        // Catatan: 'Pending' di database biasanya ditampilkan sebagai 'Ditunda' di UI
        $totalDitunda  = (clone $baseQuery)->where('status', 'Pending')->count(); 
        
        // [BARU] Tambahan untuk Berkas Ditutup
        $totalDitutup  = (clone $baseQuery)->where('status', 'Ditutup')->count();

        // 4. Hitung Jatuh Tempo (Perbaikan Logic untuk Menghindari Selisih)
        // Menggunakan whereHas lebih aman daripada join manual untuk count()
        $berkasJatuhTempoCount = (clone $baseQuery)
            ->whereIn('status', ['Diproses', 'Pending']) // Hanya berkas aktif yang bisa jatuh tempo
            ->whereHas('jenisPermohonan', function ($q) {
                // Logic: Waktu Mulai + Durasi (Hari) < Waktu Sekarang
                $q->whereRaw('DATE_ADD(berkas.waktu_mulai_proses, INTERVAL jenis_permohonans.waktu_timeline_hari DAY) < NOW()');
            })
            ->count();

        // 5. Data Berkas Terbaru untuk Tabel Dashboard (Hanya 5 Teratas)
        $berkasTerbaru = (clone $baseQuery)
            ->with(['posisiSekarang.jabatan', 'jenisPermohonan'])
            ->latest('created_at')
            ->take(5)
            ->get();

        // 6. Logika Tambahan untuk Pencarian / Mode 'Lihat Semua'
        $additionalBerkas = null;
        $searchQuery = $request->input('search');
        $isModeAll = $request->input('mode') === 'all';

        if ($searchQuery || $isModeAll) {
            $queryLanjutan = (clone $baseQuery)
                ->with(['posisiSekarang.jabatan', 'jenisPermohonan']);

            if ($searchQuery) {
                $queryLanjutan->where(function($q) use ($searchQuery) {
                    $q->where('nomer_berkas', 'like', "%{$searchQuery}%")
                      ->orWhere('nama_pemohon', 'like', "%{$searchQuery}%")
                      ->orWhere('nomer_hak', 'like', "%{$searchQuery}%")
                      ->orWhere('desa', 'like', "%{$searchQuery}%");
                });
            }
            
            $additionalBerkas = $queryLanjutan->latest('created_at')
                ->paginate(20)
                ->withQueryString();
        }

        // Mengirim semua data ke view
        return view('dashboard', compact(
            'totalBerkas',
            'totalDiproses',
            'totalSelesai',
            'totalDitunda',
            'totalDitutup', // Variabel baru dikirim ke view
            'berkasJatuhTempoCount',
            'berkasTerbaru',
            'tahun',
            'additionalBerkas',
            'searchQuery',
            'isModeAll'
        ));
    }

    /**
     * Menampilkan daftar semua berkas (total) dengan filter tahun.
     */
    public function showTotal(Request $request): View
    {
        $tahun = $request->input('tahun', date('Y'));
        
        $query = Berkas::with('posisiSekarang.jabatan')->latest();

        if ($tahun != 'Semua') {
            $query->where('tahun', $tahun);
        }

        $semuaBerkas = $query->paginate(20)->withQueryString();

        return view('detail-berkas', [
            'title' => "Total Berkas (Tahun $tahun)",
            'daftarBerkas' => $semuaBerkas,
            'tahun' => $tahun
        ]);
    }

    /**
     * Menampilkan daftar berkas yang sedang diproses.
     */
    public function showDiproses(Request $request): View
    {
        $tahun = $request->input('tahun', date('Y'));

        $query = Berkas::with('posisiSekarang.jabatan')
            ->whereIn('status', ['Diproses', 'Pending'])
            ->latest('updated_at');

        if ($tahun != 'Semua') {
            $query->where('tahun', $tahun);
        }

        $berkasDiproses = $query->paginate(20)->withQueryString();

        return view('detail-berkas', [
            'title' => "Berkas Sedang Diproses (Tahun $tahun)",
            'daftarBerkas' => $berkasDiproses,
            'tahun' => $tahun
        ]);
    }

    /**
     * Menampilkan daftar berkas yang sudah selesai.
     */
    public function showSelesai(Request $request): View
    {
        $tahun = $request->input('tahun', date('Y'));

        $query = Berkas::with('posisiSekarang.jabatan')
            ->where('status', 'Selesai')
            ->latest('waktu_selesai_proses');

        if ($tahun != 'Semua') {
            $query->where('tahun', $tahun);
        }

        $berkasSelesai = $query->paginate(20)->withQueryString();

        return view('detail-berkas', [
            'title' => "Berkas Selesai (Tahun $tahun)",
            'daftarBerkas' => $berkasSelesai,
            'tahun' => $tahun
        ]);
    }

    /**
     * Menampilkan daftar berkas yang ditutup (BARU).
     */
    public function showDitutup(Request $request): View
    {
        $tahun = $request->input('tahun', date('Y'));

        $query = Berkas::with('posisiSekarang.jabatan')
            ->where('status', 'Ditutup')
            ->latest('updated_at');

        if ($tahun != 'Semua') {
            $query->where('tahun', $tahun);
        }

        $berkasDitutup = $query->paginate(20)->withQueryString();

        return view('detail-berkas', [
            'title' => "Berkas Ditutup (Tahun $tahun)",
            'daftarBerkas' => $berkasDitutup,
            'tahun' => $tahun
        ]);
    }

    /**
     * Menampilkan daftar berkas yang sudah jatuh tempo.
     */
    public function showJatuhTempo(Request $request): View
    {
        $tahun = $request->input('tahun', date('Y'));

        // Query khusus untuk mendapatkan data lengkap jatuh tempo
        // Menggunakan logic yang sama persis dengan count di index()
        $berkasJatuhTempo = Berkas::select('berkas.*')
            ->with(['jenisPermohonan', 'posisiSekarang.jabatan'])
            ->whereIn('berkas.status', ['Diproses', 'Pending']); // Pastikan status sama dengan count

        if ($tahun != 'Semua') {
            $berkasJatuhTempo->where('berkas.tahun', $tahun);
        }

        // Join atau whereHas untuk filter tanggal
        // Kita gunakan join di sini agar bisa order by calculated field jika perlu,
        // tapi pastikan whereRaw nya sama persis dengan index()
        $berkasJatuhTempo->join('jenis_permohonans', 'berkas.jenis_permohonan_id', '=', 'jenis_permohonans.id')
            ->whereRaw('DATE_ADD(berkas.waktu_mulai_proses, INTERVAL jenis_permohonans.waktu_timeline_hari DAY) < NOW()')
            ->orderBy('berkas.waktu_mulai_proses', 'asc');

        $data = $berkasJatuhTempo->paginate(20)->withQueryString();

        return view('jatuh-tempo', [
            'berkasJatuhTempo' => $data, 
            'tahun' => $tahun
        ]);
    }
}