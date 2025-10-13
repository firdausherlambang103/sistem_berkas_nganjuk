<?php

namespace App\Http\Controllers;

use App\Models\Berkas;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Menampilkan halaman dashboard utama.
     */
    public function index(Request $request): View
    {
        // 1. Statistik Kunci
        $totalBerkas = Berkas::count();
        $totalDiproses = Berkas::whereIn('status', ['Diproses', 'Pending'])->count();
        $totalSelesai = Berkas::where('status', 'Selesai')->count();

        // Menghitung jumlah berkas yang jatuh tempo
        $berkasJatuhTempoCount = Berkas::whereIn('status', ['Diproses', 'Pending'])
            ->with('jenisPermohonan')
            ->get()
            ->filter(function ($berkas) {
                return $berkas->jatuh_tempo && Carbon::now()->greaterThan($berkas->jatuh_tempo);
            })
            ->count();

        // 2. Tabel Rincian Berkas
        $query = Berkas::query()->with(['posisiSekarang.jabatan', 'jenisPermohonan']);
        $query->whereIn('status', ['Diproses', 'Pending']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomer_berkas', 'like', "%{$search}%")
                  ->orWhere('nama_pemohon', 'like', "%{$search}%");
            });
        }
        
        $query->select('berkas.*')
            ->join('jenis_permohonans', 'berkas.jenis_permohonan_id', '=', 'jenis_permohonans.id')
            ->orderByRaw('CASE WHEN DATE_ADD(waktu_mulai_proses, INTERVAL jenis_permohonans.waktu_timeline_hari DAY) < NOW() THEN 0 ELSE 1 END ASC')
            ->orderBy('berkas.updated_at', 'desc');

        $semuaBerkasAktif = $query->paginate(10);

        return view('dashboard', compact(
            'totalBerkas',
            'totalDiproses',
            'totalSelesai',
            'berkasJatuhTempoCount', // <-- Nama variabel yang benar
            'semuaBerkasAktif'
        ));
    }

    // ... sisa fungsi lainnya tidak berubah ...
    
    /**
     * Menampilkan daftar semua berkas (total).
     */
    public function showTotal(): View
    {
        $semuaBerkas = Berkas::with('posisiSekarang.jabatan')->latest()->paginate(20);
        return view('detail-berkas', [
            'title' => 'Total Berkas',
            'daftarBerkas' => $semuaBerkas
        ]);
    }

    /**
     * Menampilkan daftar berkas yang sedang diproses.
     */
    public function showDiproses(): View
    {
        $berkasDiproses = Berkas::whereIn('status', ['Diproses', 'Pending'])
                                ->with('posisiSekarang.jabatan')
                                ->latest('updated_at')
                                ->paginate(20);
        return view('detail-berkas', [
            'title' => 'Berkas Sedang Diproses',
            'daftarBerkas' => $berkasDiproses
        ]);
    }

    /**
     * Menampilkan daftar berkas yang sudah selesai.
     */
    public function showSelesai(): View
    {
        $berkasSelesai = Berkas::where('status', 'Selesai')
                               ->with('posisiSekarang.jabatan')
                               ->latest('waktu_selesai_proses')
                               ->paginate(20);
        return view('detail-berkas', [
            'title' => 'Berkas Selesai',
            'daftarBerkas' => $berkasSelesai
        ]);
    }

    /**
     * Menampilkan daftar berkas yang sudah jatuh tempo.
     */
    public function showJatuhTempo(): View
    {
        $berkasAktif = Berkas::whereIn('status', ['Diproses', 'Pending'])
                            ->with('jenisPermohonan', 'posisiSekarang.jabatan')
                            ->get();

        $berkasJatuhTempo = $berkasAktif->filter(function ($berkas) {
            return $berkas->jatuh_tempo && Carbon::now()->greaterThan($berkas->jatuh_tempo);
        });

        return view('jatuh-tempo', compact('berkasJatuhTempo'));
    }
}

