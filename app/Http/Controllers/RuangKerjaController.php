<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Berkas;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RuangKerjaController extends Controller
{
    /**
     * Menampilkan halaman ruang kerja dengan fungsionalitas pencarian individual
     * untuk setiap tabel dan kemampuan multi-kata kunci.
     */
    public function index(Request $request): View
    {
        $currentUserId = Auth::id();

        // Mengambil input pencarian untuk setiap tabel dari request
        $searchMasuk = $request->input('search_masuk');
        $searchDiMeja = $request->input('search_di_meja');
        $searchDitunda = $request->input('search_ditunda');

        // --- 1. Query untuk Berkas Masuk ---
        // Menampilkan berkas yang dikirim ke user ini tapi belum diterima
        $berkasMenungguQuery = Berkas::where('penerima_id', $currentUserId)
            ->where('status_pengiriman', 'Dikirim')
            ->with(['pengirim.jabatan', 'jenisPermohonan', 'waLogs']);

        if ($searchMasuk) {
            $searchTerms = array_filter(array_map('trim', explode(',', $searchMasuk)));
            if (!empty($searchTerms)) {
                $berkasMenungguQuery->where(function ($query) use ($searchTerms) {
                    foreach ($searchTerms as $term) {
                        $query->orWhere('nomer_berkas', 'like', '%' . $term . '%')
                              ->orWhereHas('pengirim', function ($subQuery) use ($term) {
                                  $subQuery->where('name', 'like', '%' . $term . '%');
                              });
                    }
                });
            }
        }

        // --- 2. Query untuk Berkas di Meja Saya ---
        // Menampilkan berkas yang posisinya ada di user ini (sudah diterima & sedang diproses)
        $berkasDiMejaQuery = Berkas::where('posisi_sekarang_user_id', $currentUserId)
            ->where('status', 'Diproses')
            ->where('status_pengiriman', 'Diterima')
            // [OPTIMASI] Load relasi pendukung agar query lebih cepat (mengurangi N+1 Query)
            ->with(['jenisPermohonan', 'waLogs', 'penerimaKuasa', 'peminjamanBukuTanah']);

        if ($searchDiMeja) {
            $searchTerms = array_filter(array_map('trim', explode(',', $searchDiMeja)));
             if (!empty($searchTerms)) {
                $berkasDiMejaQuery->where(function ($query) use ($searchTerms) {
                    foreach ($searchTerms as $term) {
                        $query->orWhere('nomer_berkas', 'like', '%' . $term . '%')
                              ->orWhere('nama_pemohon', 'like', '%' . $term . '%');
                    }
                });
            }
        }

        // --- 3. Query untuk Berkas yang Ditunda (Pending) ---
        $berkasDitundaQuery = Berkas::where('posisi_sekarang_user_id', $currentUserId)
            ->where('status', 'Pending')
            ->with(['jenisPermohonan', 'waLogs']);

        if ($searchDitunda) {
            $searchTerms = array_filter(array_map('trim', explode(',', $searchDitunda)));
            if (!empty($searchTerms)) {
                $berkasDitundaQuery->where(function ($query) use ($searchTerms) {
                    foreach ($searchTerms as $term) {
                        $query->orWhere('nomer_berkas', 'like', '%' . $term . '%')
                              ->orWhere('nama_pemohon', 'like', '%' . $term . '%');
                    }
                });
            }
        }
        
        // Daftar User untuk opsi pengiriman (Kirim Ke...)
        $daftarUserTujuan = User::where('id', '!=', $currentUserId)->orderBy('name')->get();

        return view('ruang-kerja', [
            'berkasMenunggu' => $berkasMenungguQuery->latest('updated_at')->get(),
            'berkasDiMeja' => $berkasDiMejaQuery->latest('updated_at')->get(),
            'berkasDitunda' => $berkasDitundaQuery->latest('updated_at')->get(),
            'daftarUserTujuan' => $daftarUserTujuan,
        ]);
    }
}