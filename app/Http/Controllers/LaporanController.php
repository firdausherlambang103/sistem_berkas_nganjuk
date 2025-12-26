<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Jabatan;
use App\Models\RiwayatBerkas; // Pastikan Model ini di-import
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    /**
     * Menampilkan halaman laporan statistik (Index).
     */
    public function index()
    {
        $jabatans = Jabatan::with([
            'users' => function ($query) {
                // Ambil data user beserta hitungan berkasnya
                $query->withCount([
                    // Menghitung jumlah record di tabel riwayat_berkas dimana ke_user_id = user ini
                    'riwayatDiterima as total_masuk', 
                    
                    // Menghitung jumlah record di tabel riwayat_berkas dimana dari_user_id = user ini
                    'riwayatDikirim as total_keluar',

                    // Hitung sisa berkas yang masih dipegang (status Diproses/Pending)
                    'berkasDiTangan as sisa_berkas' => function ($q) {
                        $q->whereIn('status', ['Diproses', 'Pending']);
                    }
                ]);
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
     * Menampilkan rincian berkas (Masuk & Keluar) user beserta performanya.
     */
    public function showBerkasByUser(User $user)
    {
        // 1. BERKAS MASUK (INBOX)
        // Definisi: Berkas yang saat ini POSISI-nya ada di user ini (Tanggungan).
        // Menggunakan relasi 'berkasDiTangan' yang sudah terbukti jalan di halaman Index.
        $berkasMasuk = $user->berkasDiTangan()
            ->with(['jenisPermohonan']) // Load data jenis permohonan
            ->orderBy('created_at', 'desc')
            ->get();

        // 2. BERKAS KELUAR (OUTBOX / HISTORY)
        // Definisi: Riwayat di mana user ini bertindak sebagai pemroses (user_id).
        $berkasKeluar = RiwayatBerkas::with(['berkas.jenisPermohonan'])
            ->where('user_id', $user->id) // Where user ini adalah PELAKU
            ->latest()
            ->get();

        // 3. HITUNG STATISTIK
        $totalMasuk = $berkasMasuk->count(); // Beban kerja saat ini
        $totalKeluar = $berkasKeluar->count(); // Total yang sudah diselesaikan
        
        // Hitung Performa (Rasio Selesai vs Total Beban)
        // Rumus: Total Selesai / (Total Selesai + Sedang Dikerjakan)
        $totalBebanKerja = $totalKeluar + $totalMasuk;
        $persentasePenyelesaian = 0;
        
        if($totalBebanKerja > 0){
             $persentasePenyelesaian = round(($totalKeluar / $totalBebanKerja) * 100, 1);
        }

        return view('laporan.show_berkas_by_user', [
            'petugas' => $user,
            'user' => $user, // Alias untuk keamanan view
            'berkasMasuk' => $berkasMasuk,
            'berkasKeluar' => $berkasKeluar,
            'totalMasuk' => $totalMasuk,
            'totalKeluar' => $totalKeluar,
            'persentasePenyelesaian' => $persentasePenyelesaian
        ]);
    }
    
    public function showBerkasByUser1(User $user)
    {
        $daftarBerkas = $user->berkasDiTangan()->with('jenisPermohonan')->latest()->get();

        // Untuk setiap berkas, cari waktu kirim dari loket pembayaran
        foreach ($daftarBerkas as $berkas) {
            $riwayatPembayaran = $berkas->riwayat()
                ->whereHas('dariUser.jabatan', function ($query) {
                    // Cari riwayat di mana pengirimnya adalah Petugas Loket Pembayaran
                    $query->where('nama_jabatan', 'Petugas Loket Pembayaran');
                })
                ->orderBy('waktu_kirim', 'asc') // Ambil yang paling awal
                ->first();
            
            // Jika ada riwayat dari loket pembayaran, gunakan waktu kirimnya.
            // Jika tidak, gunakan waktu pembuatan berkas sebagai fallback.
            $berkas->waktu_mulai_argo = $riwayatPembayaran ? $riwayatPembayaran->waktu_kirim : $berkas->created_at;
        }

        return view('laporan.show_berkas_by_user', [
            'petugas' => $user,
            'daftarBerkas' => $daftarBerkas
        ]);
    }
}