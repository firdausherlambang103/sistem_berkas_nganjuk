<?php

namespace App\Http\Controllers;

use App\Models\PeminjamanBukuTanah;
// Pastikan Model Berkas di-import. Jika nama file model Anda berbeda, sesuaikan baris ini.
use App\Models\Berkas; 
use App\Models\Desa;
use App\Models\Kecamatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PeminjamanBukuTanahController extends Controller
{
    /**
     * Tampilkan daftar peminjaman.
     */
    public function index()
    {
        $data = PeminjamanBukuTanah::with(['desa', 'kecamatan', 'user'])
            ->latest()
            ->paginate(10);

        return view('peminjaman-bt.index', compact('data'));
    }

    /**
     * Tampilkan form tambah.
     */
    public function create()
    {
        $kecamatans = Kecamatan::orderBy('nama_kecamatan', 'asc')->get();
        $desas = Desa::orderBy('nama_desa', 'asc')->get();
        
        return view('peminjaman-bt.create', compact('kecamatans', 'desas'));
    }

    /**
     * Simpan data.
     */
    public function store(Request $request)
    {
        $request->validate([
            'jenis_hak' => 'required',
            'nomor_hak' => 'required',
            'desa_id' => 'required|exists:desas,id',
            'kecamatan_id' => 'required|exists:kecamatans,id',
            'status' => 'required',
        ]);

        PeminjamanBukuTanah::create([
            'user_id' => Auth::id(),
            'nomor_berkas' => $request->nomor_berkas,
            'jenis_hak' => $request->jenis_hak,
            'nomor_hak' => $request->nomor_hak,
            'desa_id' => $request->desa_id,
            'kecamatan_id' => $request->kecamatan_id,
            'status' => $request->status,
            'catatan' => $request->catatan,
        ]);

        return redirect()->route('peminjaman-bt.index')->with('success', 'Data berhasil disimpan.');
    }

    /**
     * AJAX: Cari data berkas (DEBUG MODE - ANTI 500 ERROR)
     */
    public function cekBerkas(Request $request)
    {
        try {
            // 1. Cek Input
            if (!$request->filled('nomor_berkas')) {
                throw new \Exception('Nomor Berkas tidak boleh kosong.');
            }

            $nomorBerkas = trim($request->query('nomor_berkas'));

            // 2. Cek Keberadaan Class Model Berkas (Untuk Debugging)
            if (!class_exists(\App\Models\Berkas::class)) {
                throw new \Exception('Model Berkas tidak ditemukan di App\Models\Berkas. Cek nama file model Anda.');
            }

            // 3. Query Database (Dibungkus try-catch database khusus)
            try {
                // Asumsi nama kolom di database adalah 'nomor_berkas'
                // Jika nama kolom Anda berbeda (misal: 'no_berkas'), ubah di sini.
                $berkas = \App\Models\Berkas::where('nomor_berkas', $nomorBerkas)->first();
            } catch (\Illuminate\Database\QueryException $e) {
                // Tangkap error kolom tidak ditemukan
                throw new \Exception('Error Database: ' . $e->getMessage());
            }

            // 4. Jika Data Kosong
            if (!$berkas) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Berkas nomor "' . $nomorBerkas . '" tidak ditemukan.'
                ]);
            }

            // 5. Ambil Data (Safe Mode - Menghindari error null object)
            // Kita gunakan logic "Safe Access" agar jika relasi null, tidak error.
            
            $desaId = $berkas->desa_id ?? null;
            $kecamatanId = $berkas->kecamatan_id ?? null;
            $nomorHak = $berkas->nomor_hak ?? '';

            // Logika Mengambil Jenis Hak
            $jenisHak = '';
            
            // Cek apakah ada kolom 'jenis_hak' langsung di tabel berkas
            if (isset($berkas->jenis_hak)) {
                $jenisHak = $berkas->jenis_hak;
            } 
            // Cek apakah ada relasi ke jenisPermohonan (pastikan nama method relasi benar di Model Berkas)
            elseif (method_exists($berkas, 'jenisPermohonan') && $berkas->jenisPermohonan) {
                // Ambil nama dari relasi
                $namaPermohonan = $berkas->jenisPermohonan->nama_jenis_permohonan ?? $berkas->jenisPermohonan->nama ?? '';
                
                // Mapping Sederhana dari Nama Permohonan ke Singkatan Hak
                if (stripos($namaPermohonan, 'Milik') !== false) $jenisHak = 'HM';
                elseif (stripos($namaPermohonan, 'Guna Bangunan') !== false) $jenisHak = 'HGB';
                elseif (stripos($namaPermohonan, 'Pakai') !== false) $jenisHak = 'HP';
                elseif (stripos($namaPermohonan, 'Guna Usaha') !== false) $jenisHak = 'HGU';
                elseif (stripos($namaPermohonan, 'Wakaf') !== false) $jenisHak = 'Wakaf';
                elseif (stripos($namaPermohonan, 'Pengelolaan') !== false) $jenisHak = 'HPL';
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'jenis_hak' => $jenisHak,
                    'nomor_hak' => $nomorHak,
                    'desa_id' => $desaId,
                    'kecamatan_id' => $kecamatanId,
                ]
            ]);

        } catch (\Throwable $e) {
            // Log error ke file storage/logs/laravel.log untuk admin
            Log::error('Error Cek Berkas BT: ' . $e->getMessage());

            // Kirim pesan error detail ke browser (agar Anda bisa baca alert-nya)
            return response()->json([
                'success' => false,
                'message' => 'SYSTEM ERROR: ' . $e->getMessage()
            ], 200); // Kita kirim 200 OK agar Javascript bisa membaca pesan JSON-nya
        }
    }
}