<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Jabatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    /**
     * Menampilkan halaman laporan statistik.
     * Logika telah diubah untuk mengelompokkan user per jabatan.
     */
    public function index()
    {
        $jabatans = Jabatan::with([
            'users' => function ($query) {
                $query->whereHas('berkasDiTangan', function ($subQuery) {
                    $subQuery->whereIn('status', ['Diproses', 'Pending']);
                })
                ->withCount(['berkasDiTangan' => function ($subQuery) {
                    $subQuery->whereIn('status', ['Diproses', 'Pending']);
                }]);
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
     * Menampilkan rincian berkas yang dipegang oleh seorang user.
     * Fungsi ini sekarang mengambil data waktu mulai "argo" dari riwayat berkas.
     */
    public function showBerkasByUser(User $user)
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

