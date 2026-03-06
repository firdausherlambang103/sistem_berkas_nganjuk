<?php

namespace App\Http\Controllers\Mitra;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Berkas;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PageController extends Controller
{
    public function dashboard()
    {
        $userId = Auth::id();

        // QUERY UTAMA: Cari berkas yang posisinya sedang di Mitra INI 
        // ATAU Mitra ini pernah menjadi pengirim/penerima di riwayat berkas tersebut.
        $baseQuery = Berkas::where(function($q) use ($userId) {
            $q->where('posisi_sekarang_user_id', $userId)
              ->orWhereHas('riwayat', function($r) use ($userId) {
                  $r->where('dari_user_id', $userId)
                    ->orWhere('ke_user_id', $userId);
              });
        });

        // 1. Total Berkas Aktif (Semua berkas yang belum 'Selesai' atau 'Ditutup')
        $berkasAktif = (clone $baseQuery)->whereNotIn('status', ['Selesai', 'Ditutup'])->count();

        // 2. Total Berkas Selesai Bulan Ini
        $selesaiBulanIni = (clone $baseQuery)->whereIn('status', ['Selesai', 'Ditutup'])
                            ->whereMonth('updated_at', Carbon::now()->month)
                            ->whereYear('updated_at', Carbon::now()->year)
                            ->count();

        // 3. Menunggu Tindakan (Berkas yang posisinya mandek di meja Mitra untuk dikerjakan/diperbaiki)
        $menungguTindakan = Berkas::where('posisi_sekarang_user_id', $userId)
                            ->where('status_pengiriman', '!=', 'Dikirim')
                            ->count();

        // 4. Ambil 10 Berkas Terbaru untuk ditampilkan di Tabel Tracker
        $berkasTerbaru = (clone $baseQuery)
                            ->with(['jenisPermohonan', 'posisiSekarang.jabatan'])
                            ->orderBy('updated_at', 'desc')
                            ->limit(10)
                            ->get();

        return view('mitra.dashboard', compact(
            'berkasAktif', 
            'selesaiBulanIni', 
            'menungguTindakan', 
            'berkasTerbaru'
        ));
    }
}