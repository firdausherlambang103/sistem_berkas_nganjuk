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
    /**
     * Tampilkan daftar peminjaman Aktif & Request Otomatis.
     */
    public function index(Request $request)
    {
        // 1. DATA PEMINJAMAN AKTIF (Belum Dikembalikan)
        $query = PeminjamanBukuTanah::with(['desa', 'kecamatan', 'user', 'berkas'])
            ->where('status', '!=', 'Dikembalikan');

        // Logika Pencarian
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

        // 2. DATA REQUEST OTOMATIS DARI RUANG KERJA
        // [PERBAIKAN] Mengganti 'Butuh' menjadi 'Sertipikat Analog' sesuai struktur database baru
        $requestOtomatis = Berkas::where('status_buku_tanah', 'Sertipikat Analog')
            ->whereDoesntHave('peminjamanBukuTanah') 
            ->with(['jenisPermohonan'])
            ->get();

        return view('peminjaman-bt.index', compact('data', 'requestOtomatis'));
    }

    /**
     * [BARU] Proses Request Otomatis menjadi Data Peminjaman Resmi.
     */
    public function prosesOtomatis($berkasId)
    {
        try {
            $berkas = Berkas::findOrFail($berkasId);

            // 1. Logika Pencarian Wilayah (Mirip cekBerkas tapi versi PHP murni)
            $kecamatanId = null;
            $desaId = null;

            // Cari Kecamatan
            $cleanKec = trim(str_ireplace(['KECAMATAN', 'KEC.', 'KEC '], '', $berkas->kecamatan));
            $kec = Kecamatan::where('nama_kecamatan', 'LIKE', "%{$cleanKec}%")->first();
            if ($kec) $kecamatanId = $kec->id;

            // Cari Desa (Persempit dengan kecamatan jika ada)
            $cleanDesa = trim(str_ireplace(['KELURAHAN', 'DESA', 'KEL.', 'DSN '], '', $berkas->desa));
            $queryDesa = Desa::where('nama_desa', 'LIKE', "%{$cleanDesa}%");
            if ($kecamatanId) $queryDesa->where('kecamatan_id', $kecamatanId);
            $des = $queryDesa->first();
            if ($des) $desaId = $des->id;

            // Validasi jika master data tidak cocok
            if (!$kecamatanId || !$desaId) {
                return redirect()->back()->with('error', 'Gagal memproses otomatis: Nama Wilayah (Kec/Desa) pada berkas tidak ditemukan di Master Data.');
            }

            // 2. Mapping Jenis Hak (Parsing String)
            $jenisHak = $berkas->jenis_alas_hak;
            // Jika kolom jenis_alas_hak kosong/tidak sesuai, gunakan parsing dari jenis permohonan
            if (empty($jenisHak) || strlen($jenisHak) > 5) {
                $rawJenis = $berkas->jenisPermohonan->nama_permohonan ?? '';
                if (stripos($rawJenis, 'Milik') !== false) $jenisHak = 'HM';
                elseif (stripos($rawJenis, 'Guna Bangunan') !== false) $jenisHak = 'HGB';
                elseif (stripos($rawJenis, 'Pakai') !== false) $jenisHak = 'HP';
                elseif (stripos($rawJenis, 'Guna Usaha') !== false) $jenisHak = 'HGU';
                elseif (stripos($rawJenis, 'Wakaf') !== false) $jenisHak = 'Wakaf';
                elseif (stripos($rawJenis, 'Pengelolaan') !== false) $jenisHak = 'HPL';
                else $jenisHak = 'HM'; // Default fallback
            }

            // 3. Simpan ke Peminjaman
            PeminjamanBukuTanah::create([
                'user_id'       => Auth::id(),
                'berkas_id'     => $berkas->id,
                'nomor_berkas'  => $berkas->nomer_berkas,
                'jenis_hak'     => $jenisHak,
                'nomor_hak'     => $berkas->nomer_hak,
                'desa_id'       => $desaId,
                'kecamatan_id'  => $kecamatanId,
                'status'        => 'Surat Tugas 1', // Default status awal proses
                'catatan'       => 'Request Otomatis dari Berkas: ' . $berkas->nomer_berkas,
            ]);

            return redirect()->route('peminjaman-bt.index')->with('success', 'Request berkas berhasil diproses menjadi peminjaman.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Tampilkan Riwayat Peminjaman (Hanya yang SUDAH dikembalikan).
     */
    public function riwayat(Request $request)
    {
        $query = PeminjamanBukuTanah::with(['desa', 'kecamatan', 'user'])
            ->where('status', 'Dikembalikan');

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
        $request->validate([
            'jenis_hak' => 'required',
            'nomor_hak' => 'required',
            'desa_id' => 'required',
            'kecamatan_id' => 'required',
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

        $dataToUpdate = [
            'jenis_hak' => $request->jenis_hak,
            'nomor_hak' => $request->nomor_hak,
            'desa_id' => $request->desa_id,
            'kecamatan_id' => $request->kecamatan_id,
            'status' => $request->status,
            'catatan' => $request->catatan,
        ];

        // Hack untuk menangani potensi perbedaan nama kolom di database
        $dataToUpdate['nomor_berkas'] = $request->nomor_berkas;

        try {
            $item->update($dataToUpdate);
        } catch (\Illuminate\Database\QueryException $e) {
            if (str_contains($e->getMessage(), 'Unknown column')) {
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

            $txtKecamatan = $berkas->kecamatan;
            $txtDesa = $berkas->desa;

            // A. Cari Kecamatan
            if (!empty($txtKecamatan)) {
                $cleanKec = trim(str_ireplace(['KECAMATAN', 'KEC.', 'KEC '], '', $txtKecamatan));
                $kec = Kecamatan::where('nama_kecamatan', 'LIKE', "%{$cleanKec}%")->first();
                if ($kec) {
                    $kecamatanId = $kec->id;
                }
            }

            // B. Cari Desa
            if (!empty($txtDesa)) {
                $cleanDesa = trim(str_ireplace(['KELURAHAN', 'DESA', 'KEL.', 'DSN '], '', $txtDesa));
                $queryDesa = Desa::where('nama_desa', 'LIKE', "%{$cleanDesa}%");

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