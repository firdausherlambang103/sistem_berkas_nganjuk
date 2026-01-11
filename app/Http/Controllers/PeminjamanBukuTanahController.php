<?php

namespace App\Http\Controllers;

use App\Models\PeminjamanBukuTanah;
use App\Models\Berkas; 
use App\Models\Desa;
use App\Models\Kecamatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PeminjamanBukuTanahController extends Controller
{
    // ... Method index, riwayat, create, store, cekBerkas (GUNAKAN YG LAMA / TIDAK PERLU DIUBAH) ...
    // HANYA PASTIKAN METHOD DI BAWAH INI ADA & BENAR:

    /**
     * Tampilkan daftar peminjaman (Hanya yang BELUM dikembalikan).
     */
/**
     * Tampilkan daftar peminjaman (Hanya yang BELUM dikembalikan).
     */
    public function index(Request $request)
    {
        $query = PeminjamanBukuTanah::with(['desa', 'kecamatan', 'user'])
            ->where('status', '!=', 'Dikembalikan');

        // LOGIKA PENCARIAN
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nomor_berkas', 'like', "%{$search}%")
                  ->orWhere('nomor_hak', 'like', "%{$search}%")
                  ->orWhere('jenis_hak', 'like', "%{$search}%")
                  ->orWhere('catatan', 'like', "%{$search}%")
                  ->orWhereHas('desa', function($d) use ($search) {
                      $d->where('nama_desa', 'like', "%{$search}%");
                  })
                  ->orWhereHas('kecamatan', function($k) use ($search) {
                      $k->where('nama_kecamatan', 'like', "%{$search}%");
                  });
            });
        }

        $data = $query->latest()->paginate(10);

        return view('peminjaman-bt.index', compact('data'));
    }

    /**
     * Tampilkan Riwayat Peminjaman (Hanya yang SUDAH dikembalikan).
     */
    public function riwayat(Request $request)
    {
        $query = PeminjamanBukuTanah::with(['desa', 'kecamatan', 'user'])
            ->where('status', 'Dikembalikan');

        // LOGIKA PENCARIAN
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nomor_berkas', 'like', "%{$search}%")
                  ->orWhere('nomor_hak', 'like', "%{$search}%")
                  ->orWhere('jenis_hak', 'like', "%{$search}%")
                  ->orWhere('catatan', 'like', "%{$search}%")
                  ->orWhereHas('desa', function($d) use ($search) {
                      $d->where('nama_desa', 'like', "%{$search}%");
                  })
                  ->orWhereHas('kecamatan', function($k) use ($search) {
                      $k->where('nama_kecamatan', 'like', "%{$search}%");
                  });
            });
        }

        $data = $query->latest('updated_at')->paginate(10);

        return view('peminjaman-bt.riwayat', compact('data'));
    }

    public function create()
    {
        $kecamatans = Kecamatan::orderBy('nama_kecamatan', 'asc')->get();
        $desas = Desa::orderBy('nama_desa', 'asc')->get();
        return view('peminjaman-bt.create', compact('kecamatans', 'desas'));
    }

    public function store(Request $request)
    {
        // Validasi
        $request->validate([
            'jenis_hak' => 'required',
            'nomor_hak' => 'required',
            'desa_id' => 'required',
            'kecamatan_id' => 'required',
            'status' => 'required',
        ]);

        // Simpan Data (Sesuaikan nama kolom 'nomer_berkas' dengan DB Anda)
        PeminjamanBukuTanah::create([
            'user_id' => Auth::id(),
            'nomor_berkas' => $request->nomor_berkas, // Pastikan ini sesuai kolom DB (nomor_berkas atau nomer_berkas)
            'jenis_hak' => $request->jenis_hak,
            'nomor_hak' => $request->nomor_hak,
            'desa_id' => $request->desa_id,
            'kecamatan_id' => $request->kecamatan_id,
            'status' => $request->status,
            'catatan' => $request->catatan,
        ]);

        return redirect()->route('peminjaman-bt.index')->with('success', 'Data berhasil disimpan.');
    }

    // --- BAGIAN EDIT & UPDATE YANG PENTING ---

    /**
     * Tampilkan form Edit.
     */
    public function edit($id)
    {
        try {
            $item = PeminjamanBukuTanah::findOrFail($id);
            $kecamatans = Kecamatan::orderBy('nama_kecamatan', 'asc')->get();
            $desas = Desa::orderBy('nama_desa', 'asc')->get();

            return view('peminjaman-bt.edit', compact('item', 'kecamatans', 'desas'));
        } catch (\Exception $e) {
            return redirect()->route('peminjaman-bt.index')->with('error', 'Data tidak ditemukan: ' . $e->getMessage());
        }
    }

    /**
     * Update data (Termasuk Pengembalian).
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'jenis_hak' => 'required',
            'nomor_hak' => 'required',
            'desa_id' => 'required',
            'kecamatan_id' => 'required',
            'status' => 'required',
        ]);

        $item = PeminjamanBukuTanah::findOrFail($id);

        // Data yang akan diupdate
        $dataToUpdate = [
            'jenis_hak' => $request->jenis_hak,
            'nomor_hak' => $request->nomor_hak,
            'desa_id' => $request->desa_id,
            'kecamatan_id' => $request->kecamatan_id,
            'status' => $request->status,
            'catatan' => $request->catatan,
        ];

        // Tambahkan nomor_berkas (Menangani kemungkinan beda nama kolom)
        // Cek apakah model punya kolom 'nomer_berkas' atau 'nomor_berkas'
        // Ini hack sederhana: kita coba masukkan 'nomor_berkas'.
        // Jika di model Anda sudah diset guarded=[], maka aman.
        $dataToUpdate['nomor_berkas'] = $request->nomor_berkas;

        // Lakukan Update
        try {
            $item->update($dataToUpdate);
        } catch (\Illuminate\Database\QueryException $e) {
            // Jika error kolom tidak ditemukan, coba nama lain (nomer vs nomor)
            if (str_contains($e->getMessage(), 'Unknown column')) {
                // Hapus key yang salah, ganti dengan yang benar (misal nomer_berkas)
                unset($dataToUpdate['nomor_berkas']);
                $dataToUpdate['nomer_berkas'] = $request->nomor_berkas; 
                $item->update($dataToUpdate);
            } else {
                throw $e;
            }
        }

        if ($request->status == 'Dikembalikan') {
            return redirect()->route('peminjaman-bt.riwayat')->with('success', 'Buku Tanah berhasil dikembalikan.');
        }

        return redirect()->route('peminjaman-bt.index')->with('success', 'Data berhasil diperbarui.');
    }
    
/**
     * AJAX: Cari data berkas (SMART SEARCH WILAYAH)
     */
    public function cekBerkas(Request $request)
    {
        try {
            if (!$request->filled('nomor_berkas')) {
                throw new \Exception('Nomor Berkas tidak boleh kosong.');
            }

            $inputNomor = trim($request->query('nomor_berkas'));
            $berkas = \App\Models\Berkas::where('nomer_berkas', $inputNomor)->first();

            if (!$berkas) {
                return response()->json(['success' => false, 'message' => 'Berkas tidak ditemukan.']);
            }

            // === 1. LOGIKA MAPPING WILAYAH PINTAR ===
            $kecamatanId = null;
            $desaId = null;

            // Ambil teks asli dari tabel Berkas
            $txtKecamatan = $berkas->kecamatan; // Misal: "KEC. MOJOROTO"
            $txtDesa = $berkas->desa;           // Misal: "KEL. BANDAR LOR"

            // A. Cari Kecamatan (Hapus awalan KEC/KECAMATAN agar pencarian lebih luas)
            if (!empty($txtKecamatan)) {
                // Hapus kata 'KECAMATAN', 'KEC.', 'KEC ' (Case Insensitive)
                $cleanKec = trim(str_ireplace(['KECAMATAN', 'KEC.', 'KEC '], '', $txtKecamatan));
                
                // Cari yang namanya mirip
                $kec = Kecamatan::where('nama_kecamatan', 'LIKE', "%{$cleanKec}%")->first();
                
                if ($kec) {
                    $kecamatanId = $kec->id;
                }
            }

            // B. Cari Desa (Hapus awalan DESA/KELURAHAN)
            if (!empty($txtDesa)) {
                // Hapus kata 'DESA', 'KELURAHAN', 'KEL.', 'DSN'
                $cleanDesa = trim(str_ireplace(['KELURAHAN', 'DESA', 'KEL.', 'DSN '], '', $txtDesa));
                
                $queryDesa = Desa::where('nama_desa', 'LIKE', "%{$cleanDesa}%");

                // Jika Kecamatan sudah ketemu, persempit pencarian desa HANYA di kecamatan tersebut
                // (Mencegah salah ambil jika ada nama desa yang sama di kecamatan lain)
                if ($kecamatanId) {
                    $queryDesa->where('kecamatan_id', $kecamatanId);
                }

                $des = $queryDesa->first();
                if ($des) {
                    $desaId = $des->id;
                }
            }

            // === 2. LOGIKA MAPPING HAK ===
            $rawJenis = $berkas->jenis_alas_hak ?? $berkas->jenis_permohonan ?? '';
            $jenisHak = '';

            if (stripos($rawJenis, 'Milik') !== false) $jenisHak = 'HM';
            elseif (stripos($rawJenis, 'Guna Bangunan') !== false) $jenisHak = 'HGB';
            elseif (stripos($rawJenis, 'Pakai') !== false) $jenisHak = 'HP';
            elseif (stripos($rawJenis, 'Guna Usaha') !== false) $jenisHak = 'HGU';
            elseif (stripos($rawJenis, 'Wakaf') !== false) $jenisHak = 'Wakaf';
            elseif (stripos($rawJenis, 'Pengelolaan') !== false) $jenisHak = 'HPL';

            $nomorHak = $berkas->nomer_hak ?? $berkas->nomor_hak ?? '';

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
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 200);
        }
    }
}