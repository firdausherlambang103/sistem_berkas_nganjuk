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
        $currentUser = Auth::user();
        $currentUserId = $currentUser->id;

        // Cek Role apakah Petugas Loket Pembayaran
        $isLoketPembayaran = optional($currentUser->jabatan)->nama_jabatan === 'Petugas Loket Pembayaran';

        // Mengambil input pencarian & filter dari request
        $searchMasuk = $request->input('search_masuk');
        $searchDiMeja = $request->input('search_di_meja');
        $searchDitunda = $request->input('search_ditunda');
        $filterPembayaran = $request->input('filter_pembayaran'); // [BARU] Filter status pembayaran

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

        // [BARU] Logika Filter Pembayaran (Hanya berfungsi jika user adalah Petugas Loket Pembayaran)
        if ($isLoketPembayaran && $filterPembayaran) {
            if ($filterPembayaran === 'belum') {
                $berkasDiMejaQuery->whereNull('tgl_bayar');
            } elseif ($filterPembayaran === 'sudah') {
                $berkasDiMejaQuery->whereNotNull('tgl_bayar');
            }
        }

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
        
        // --- 4. Query untuk Daftar User Tujuan (Dropdown 'Kirim Ke...') ---
        $daftarUserTujuanQuery = User::where('id', '!=', $currentUserId)
            ->where('is_approved', true) // Pastikan hanya user aktif yang muncul
            ->with('jabatan');

        // Cek apakah user yang login saat ini adalah Mitra (PPAT / Freelance)
        $isMitra = $currentUser->jabatan && ($currentUser->jabatan->is_mitra || in_array($currentUser->jabatan->nama_jabatan, ['PPAT', 'Freelance']));

        if ($isMitra) {
            // [ATURAN KHUSUS MITRA]
            // Jika Mitra, HANYA BISA mengirim berkas ke user dengan Jabatan tertentu
            // Silakan sesuaikan array nama_jabatan di bawah ini sesuai SOP Kantor Anda
            $daftarUserTujuanQuery->whereHas('jabatan', function ($query) {
                $query->whereIn('nama_jabatan', [
                    'Petugas Loket Entri', 
                    'Loket Pelayanan Penyerahan'
                ]);
            });
        } else {
            // [ATURAN PEGAWAI INTERNAL]
            // (Opsional) Mencegah pegawai internal mengirim berkas ke akun Mitra, 
            // buka komentar (uncomment) blok di bawah ini jika diperlukan:
            /*
            $daftarUserTujuanQuery->whereHas('jabatan', function ($query) {
                $query->where('is_mitra', false);
            });
            */
        }

        // Ambil data tujuan pengiriman
        $daftarUserTujuan = $daftarUserTujuanQuery->orderBy('name')->get();

        return view('ruang-kerja', [
            'berkasMenunggu' => $berkasMenungguQuery->latest('updated_at')->get(),
            'berkasDiMeja' => $berkasDiMejaQuery->latest('updated_at')->get(),
            'berkasDitunda' => $berkasDitundaQuery->latest('updated_at')->get(),
            'daftarUserTujuan' => $daftarUserTujuan,
            'isLoketPembayaran' => $isLoketPembayaran, // [BARU] Melempar variabel role ke view
        ]);
    }
}