<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PetugasUkur;
use App\Models\Berkas;
use App\Models\JadwalUkur;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class JadwalUkurController extends Controller
{
    /**
     * Method untuk halaman utama modul (dashboard umum penjadwalan).
     */
    public function index(): View
    {
        return view('jadwal-ukur.dashboard');
    }

    /**
     * Menampilkan dashboard khusus untuk petugas ukur dengan diagram beban kerja.
     */
 public function dashboardPetugas(): View
    {
        // Mengambil semua petugas ukur dan menghitung jumlah berkas aktif di tangan mereka
        $petugasData = PetugasUkur::with('user')
            ->get()
            ->map(function ($petugas) {
                $bebanKerja = Berkas::where('posisi_sekarang_user_id', $petugas->user_id)
                                    ->whereIn('status', ['Diproses', 'Pending'])
                                    ->count();
                return [
                    'nama' => $petugas->user->name,
                    'beban_kerja' => $bebanKerja,
                ];
            })
            // --- PERUBAHAN DI SINI: Mengurutkan dari beban kerja terbanyak ---
            ->sortByDesc('beban_kerja');

        // Menyiapkan data untuk chart
        $chartLabels = $petugasData->pluck('nama')->values()->toJson();
        $chartData = $petugasData->pluck('beban_kerja')->values()->toJson();

        return view('jadwal-ukur.dashboard-petugas', compact('chartLabels', 'chartData'));
    }

    /**
     * Menampilkan halaman pemilihan petugas dengan perhitungan beban kerja yang akurat.
     * Fungsi ini sekarang dapat menerima ID berkas opsional dari alur pembuatan berkas baru.
     */
    public function pilihPetugas(Berkas $berkas = null): View
    {
        $semuaPetugas = PetugasUkur::with(['user.jabatan', 'areaKerja'])
            ->join('users', 'petugas_ukur.user_id', '=', 'users.id')
            ->orderBy('users.name', 'asc')
            ->select('petugas_ukur.*') 
            ->get();
        
        // Hitung beban kerja (berkas di meja) untuk setiap petugas
        foreach ($semuaPetugas as $petugas) {
            $petugas->beban_berkas_count = Berkas::where('posisi_sekarang_user_id', $petugas->user_id)
                                                 ->whereIn('status', ['Diproses', 'Pending'])
                                                 ->count();
        }
        
        // Kirim objek berkas (jika ada) ke view
        return view('jadwal-ukur.pilih-petugas', [
            'semuaPetugas' => $semuaPetugas,
            'berkas' => $berkas
        ]);
    }

    /**
     * Menampilkan form untuk menginput jadwal bagi petugas yang dipilih.
     * Fungsi ini sekarang dapat menerima ID berkas opsional untuk mengisi form secara otomatis.
     */
    public function inputJadwal(PetugasUkur $petugasUkur, Berkas $berkas = null): View
    {
        $berkasAktif = Berkas::where('status', 'Diproses')->orderBy('nomer_berkas')->get();

        return view('jadwal-ukur.input-jadwal', [
            'petugas' => $petugasUkur,
            'berkasAktif' => $berkasAktif,
            'berkasDipilih' => $berkas // Kirim berkas yang sudah dipilih (jika ada)
        ]);
    }

    /**
     * Menyimpan data jadwal ukur yang baru ke database.
     */
    public function simpanJadwal(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'berkas_id' => 'required|exists:berkas,id',
            'petugas_ukur_id' => 'required|exists:petugas_ukur,id',
            'no_surat_tugas' => 'nullable|string|max:255',
            'tanggal_rencana_ukur' => 'required|date',
        ]);

        JadwalUkur::create($validatedData);

        return redirect()->route('jadwal-ukur.index')->with('success', 'Jadwal ukur berhasil dibuat!');
    }
}

