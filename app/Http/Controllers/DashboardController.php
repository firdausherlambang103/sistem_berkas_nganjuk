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

        // 2. Query Dasar (Difilter berdasarkan tahun pembuatan berkas)
        $baseQuery = Berkas::where('tahun', $tahun);

        // 3. Hitung Statistik
        $totalBerkas = (clone $baseQuery)->count();
        $totalDiproses = (clone $baseQuery)->where('status', 'Diproses')->count();
        $totalSelesai = (clone $baseQuery)->where('status', 'Selesai')->count();
        $totalDitunda = (clone $baseQuery)->where('status', 'Pending')->count();
        $totalDitutup = (clone $baseQuery)->where('status', 'Ditutup')->count();

        // 4. Hitung Jatuh Tempo (Lebih efisien via Query daripada Filter Collection)
        // Logika: Status aktif (Diproses/Pending) DAN (waktu_mulai + durasi < sekarang)
        $berkasJatuhTempoCount = (clone $baseQuery)
            ->whereIn('status', ['Diproses', 'Pending'])
            ->join('jenis_permohonans', 'berkas.jenis_permohonan_id', '=', 'jenis_permohonans.id')
            ->whereRaw('DATE_ADD(berkas.waktu_mulai_proses, INTERVAL jenis_permohonans.waktu_timeline_hari DAY) < NOW()')
            ->count();

        // 5. Data Berkas Terbaru untuk Tabel Dashboard (Hanya 5 Teratas)
        $berkasTerbaru = (clone $baseQuery)
            ->with(['posisiSekarang.jabatan', 'jenisPermohonan'])
            ->latest('created_at') // Urutkan dari yang paling baru dibuat
            ->take(5) // Ambil 5 data saja untuk dashboard
            ->get();

        // 6. [BARU] Logika Tambahan untuk Pencarian / Lihat Semua
        $additionalBerkas = null;
        $searchQuery = $request->input('search');
        $isModeAll = $request->input('mode') === 'all';

        // Jika ada pencarian ATAU mode 'all' aktif, jalankan query tambahan
        if ($searchQuery || $isModeAll) {
            $queryLanjutan = (clone $baseQuery) // Clone lagi agar filter tahun tetap terbawa
                ->with(['posisiSekarang.jabatan', 'jenisPermohonan']);

            // Jika sedang mencari, tambahkan kondisi filter
            if ($searchQuery) {
                $queryLanjutan->where(function($q) use ($searchQuery) {
                    $q->where('nomer_berkas', 'like', "%{$searchQuery}%")
                      ->orWhere('nama_pemohon', 'like', "%{$searchQuery}%")
                      ->orWhere('nomer_hak', 'like', "%{$searchQuery}%")
                      ->orWhere('desa', 'like', "%{$searchQuery}%");
                });
            }
            
            // Ambil data dengan pagination (misal 20 per halaman)
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
            'totalDitutup',
            'berkasJatuhTempoCount',
            'berkasTerbaru',
            'tahun',
            // Variabel Baru yang dikirim ke View
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
        
        $semuaBerkas = Berkas::with('posisiSekarang.jabatan')
                             ->where('tahun', $tahun)
                             ->latest()
                             ->paginate(20)
                             ->withQueryString();

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

        $berkasDiproses = Berkas::where('tahun', $tahun)
                                ->whereIn('status', ['Diproses', 'Pending'])
                                ->with('posisiSekarang.jabatan')
                                ->latest('updated_at')
                                ->paginate(20)
                                ->withQueryString();

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

        $berkasSelesai = Berkas::where('tahun', $tahun)
                               ->where('status', 'Selesai')
                               ->with('posisiSekarang.jabatan')
                               ->latest('waktu_selesai_proses')
                               ->paginate(20)
                               ->withQueryString();

        return view('detail-berkas', [
            'title' => "Berkas Selesai (Tahun $tahun)",
            'daftarBerkas' => $berkasSelesai,
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
        $berkasJatuhTempo = Berkas::select('berkas.*')
            ->where('berkas.tahun', $tahun)
            ->whereIn('berkas.status', ['Diproses', 'Pending'])
            ->join('jenis_permohonans', 'berkas.jenis_permohonan_id', '=', 'jenis_permohonans.id')
            ->whereRaw('DATE_ADD(berkas.waktu_mulai_proses, INTERVAL jenis_permohonans.waktu_timeline_hari DAY) < NOW()')
            ->with(['jenisPermohonan', 'posisiSekarang.jabatan'])
            ->orderBy('waktu_mulai_proses', 'asc') // Urutkan dari yang paling lama lewat
            ->paginate(20)
            ->withQueryString();

        return view('jatuh-tempo', compact('berkasJatuhTempo', 'tahun'));
    }
}