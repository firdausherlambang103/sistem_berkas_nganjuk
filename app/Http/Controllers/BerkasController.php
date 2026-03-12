<?php

namespace App\Http\Controllers;

use App\Models\Berkas;
use App\Models\RiwayatBerkas;
use App\Models\User;
use App\Models\Kecamatan;
use App\Models\JenisPermohonan;
use App\Models\PenerimaKuasa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;

/**
 * Controller BerkasController
 * Mengelola semua logika bisnis yang terkait dengan berkas,
 * mulai dari pembuatan, pergerakan, hingga penyelesaian.
 */
class BerkasController extends Controller
{
    /**
     * Menampilkan form untuk membuat berkas baru.
     */
    public function create(): View
    {
        $kecamatans = Kecamatan::orderBy('nama_kecamatan')->get();
        $jenisPermohonans = JenisPermohonan::orderBy('nama_permohonan')->get();
        $penerimaKuasas = PenerimaKuasa::orderBy('nama_kuasa')->get();
        
        $user = Auth::user();
        $isMitra = $user->jabatan && ($user->jabatan->is_mitra || in_array($user->jabatan->nama_jabatan, ['PPAT', 'Freelance']));
        
        // Generate kode acak 6 digit huruf besar & angka (Bisa diedit nantinya di view)
        $generatedCode = strtoupper(substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 6));
        
        return view('berkas.create', compact('kecamatans', 'jenisPermohonans', 'penerimaKuasas', 'isMitra', 'user', 'generatedCode'));
    }

    /**
     * Menyimpan berkas baru dan mengarahkan kembali ke Ruang Kerja.
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. Validasi Input
        $validatedData = $request->validate([
            'tahun' => 'required|digits:4|integer|min:2000|max:'.(date('Y')+1),
            'nomer_berkas' => [
                'required',
                'string',
                'max:255',
                // Cek unik kombinasi nomer + tahun
                Rule::unique('berkas')->where(function ($query) use ($request) {
                    return $query->where('tahun', $request->tahun);
                }),
            ],
            'nama_pemohon' => 'required|string|max:255',
            'jenis_alas_hak' => 'required|string|max:255',
            'nomer_hak' => 'required|string|max:255',
            'jenis_permohonan_id' => 'required|exists:jenis_permohonans,id',
            'kecamatan' => 'required|string|max:255',
            'desa' => 'required|string|max:255',
            'nomer_wa' => 'nullable|string|max:20',
            'penerima_kuasa_id' => 'nullable|exists:penerima_kuasas,id',
            'catatan' => 'nullable|string',
            'status_buku_tanah' => 'required|in:Sertipikat Elektronik,Sertipikat Analog,Belum Sertipikat', 
            
            // Validasi File, Foto Lokasi & Koordinat
            'file_sertipikat' => 'nullable|mimes:pdf|max:5120', 
            'file_data_pendukung' => 'nullable|mimes:pdf|max:5120',
            'foto_lokasi' => 'nullable|image|max:5120', // Validasi foto lokasi (maksimal 5MB)
            'file_sps' => 'nullable|mimes:pdf|max:5120',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $berkas = null; 

        try {
            DB::transaction(function () use ($request, $validatedData, &$berkas) {
                $currentUser = Auth::user();

                // Proses Upload File jika ada
                $pathSertipikat = null;
                if ($request->hasFile('file_sertipikat')) {
                    $pathSertipikat = $request->file('file_sertipikat')->store('berkas/sertipikat', 'public');
                }

                $pathDataPendukung = null;
                if ($request->hasFile('file_data_pendukung')) {
                    $pathDataPendukung = $request->file('file_data_pendukung')->store('berkas/data_pendukung', 'public');
                }

                // Proses Upload Foto Lokasi (Mitra)
                $pathFotoLokasi = null;
                if ($request->hasFile('foto_lokasi')) {
                    $pathFotoLokasi = $request->file('foto_lokasi')->store('berkas/foto_lokasi', 'public');
                }

                // 2. Buat Data Berkas
                $berkas = Berkas::create([
                    'tahun' => $validatedData['tahun'],
                    'nomer_berkas' => $validatedData['nomer_berkas'],
                    'nama_pemohon' => $validatedData['nama_pemohon'],
                    'jenis_alas_hak' => $validatedData['jenis_alas_hak'],
                    'nomer_hak' => $validatedData['nomer_hak'],
                    'jenis_permohonan_id' => $validatedData['jenis_permohonan_id'],
                    'kecamatan' => $validatedData['kecamatan'],
                    'desa' => $validatedData['desa'],
                    'nomer_wa' => $validatedData['nomer_wa'],
                    'penerima_kuasa_id' => $validatedData['penerima_kuasa_id'] ?? null,
                    'catatan' => $validatedData['catatan'],
                    'status_buku_tanah' => $validatedData['status_buku_tanah'],
                    
                    // Set default values
                    'posisi_sekarang_user_id' => $currentUser->id,
                    'status' => 'Diproses',
                    'status_pengiriman' => 'Diterima',
                    'pengirim_id' => $currentUser->id, // Pengirim awal adalah pembuat
                    'waktu_mulai_proses' => null, 
                    
                    // Simpan Data File dan Lokasi ke tabel
                    'file_sertipikat' => $pathSertipikat,
                    'file_data_pendukung' => $pathDataPendukung,
                    'foto_lokasi' => $pathFotoLokasi, // Simpan path foto lokasi
                    'latitude' => $validatedData['latitude'] ?? null,
                    'longitude' => $validatedData['longitude'] ?? null,
                ]);

                // 3. Catat Riwayat Pembuatan
                RiwayatBerkas::create([
                    'berkas_id' => $berkas->id,
                    'dari_user_id' => $currentUser->id,
                    'ke_user_id' => $currentUser->id,
                    'waktu_kirim' => now(),
                    'catatan_pengiriman' => 'Berkas baru dibuat dan masuk ke ruang kerja pembuat.'
                ]);
            });

        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan berkas. Error: ' . $e->getMessage());
        }

        return redirect()->route('ruang-kerja')
                         ->with('success', 'Berkas baru berhasil dibuat!');
    }
    
    /**
     * Menampilkan detail dan riwayat berkas.
     */
    public function show(Berkas $berkas): View
    {
        // Load relasi penting untuk tampilan detail
        $berkas->load([
            'riwayat.dariUser.jabatan', 
            'riwayat.keUser.jabatan', 
            'posisiSekarang.jabatan', 
            'jenisPermohonan',
            'dataDesa',       
            'dataKecamatan',  
            'penerimaKuasa'
        ]);

        // Pencarian atribut dibuat menjadi ILIKE text agar tidak error tipe JSON
        // Ini memastikan nomer berkas terbaca apapun format key-nya
        $spatialFeature = DB::connection('pgsql')->table('spatial_features')
            ->where('name', $berkas->nomer_berkas)
            ->orWhereRaw("properties::text ILIKE ?", ['%"' . $berkas->nomer_berkas . '"%'])
            ->select(DB::raw("ST_AsGeoJSON(geom) as geometry"))
            ->first();

        // Jika ketemu, simpan geometrinya. Jika tidak, set null.
        $geojsonGeometry = $spatialFeature ? $spatialFeature->geometry : null;
        
        return view('berkas.show', compact('berkas', 'geojsonGeometry'));
    }

    /**
     * Menampilkan form untuk mengedit berkas.
     */
    public function edit(Berkas $berkas)
    {
        $user = Auth::user();
        
        // Logika Pengecekan KETAT
        $isAdmin = optional($user->jabatan)->is_admin;
        $hasSpecialAccess = method_exists($user, 'hasMenuAccess') && $user->hasMenuAccess('edit_berkas');

        if (!$isAdmin && !$hasSpecialAccess) {
            return redirect()->route('ruang-kerja')->with('error', 'Anda tidak memiliki hak akses untuk mengedit berkas. Hubungi Admin.');
        }

        $kecamatans = Kecamatan::orderBy('nama_kecamatan')->get();
        $jenisPermohonans = JenisPermohonan::orderBy('nama_permohonan')->get();
        $penerimaKuasas = PenerimaKuasa::orderBy('nama_kuasa')->get();

        return view('berkas.edit', compact('berkas', 'kecamatans', 'jenisPermohonans', 'penerimaKuasas'));
    }
    
    /**
     * Memperbarui data berkas di database.
     */
    public function update(Request $request, Berkas $berkas): RedirectResponse
    {
        $user = Auth::user();
        
        // Logika Pengecekan KETAT
        $isAdmin = optional($user->jabatan)->is_admin;
        $hasSpecialAccess = method_exists($user, 'hasMenuAccess') && $user->hasMenuAccess('edit_berkas');

        if (!$isAdmin && !$hasSpecialAccess) {
            abort(403, 'Tindakan tidak diizinkan. Anda tidak punya hak akses edit berkas.');
        }

        $validatedData = $request->validate([
            'tahun' => 'required|digits:4|integer',
            'nomer_berkas' => [
                'required',
                'string',
                'max:255',
                // Cek unik kecuali ID ini sendiri
                Rule::unique('berkas')->where(function ($query) use ($request) {
                    return $query->where('tahun', $request->tahun);
                })->ignore($berkas->id),
            ],
            'nama_pemohon' => 'required|string|max:255',
            'jenis_alas_hak' => 'required|string|max:255',
            'nomer_hak' => 'required|string|max:255',
            'jenis_permohonan_id' => 'required|exists:jenis_permohonans,id',
            'kecamatan' => 'required|string|max:255',
            'desa' => 'required|string|max:255',
            'nomer_wa' => 'nullable|string|max:20',
            'penerima_kuasa_id' => 'nullable|exists:penerima_kuasas,id',
            'catatan' => 'nullable|string',
            'status_buku_tanah' => 'required|in:Sertipikat Elektronik,Sertipikat Analog,Belum Sertipikat',
        ]);

        try {
            $berkas->update($validatedData);
            return redirect()->route('ruang-kerja')->with('success', 'Data berkas berhasil diperbarui!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal memperbarui berkas: ' . $e->getMessage());
        }
    }

    public function destroy(Berkas $berkas): RedirectResponse
    {
        try {
            $berkas->delete();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus berkas. Error: ' . $e->getMessage());
        }
        return redirect()->route('dashboard')->with('success', 'Berkas berhasil dihapus!');
    }

    /**
     * Mengirim berkas ke user lain.
     */
    public function kirim(Request $request): RedirectResponse
    {
        $request->validate([
            'berkas_ids' => 'required|string',
            'tujuan_user_id' => 'required|exists:users,id',
            'catatan_pengiriman' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $berkasIds = explode(',', $request->berkas_ids);
                $pengirim = Auth::user();
                
                $jabatanPengirim = optional($pengirim->jabatan)->nama_jabatan;
                
                // Petugas Loket Pembayaran DIHAPUS dari pemicu Timer Pengiriman
                // Karena Timer Argo SLA sudah pindah ke fungsi "Sudah Dibayar" (KwitansiController)
                $jabatanPemicuTimer = [
                    'Petugas Loket Alih Media'
                ];

                $berkasDikirimCount = 0;

                foreach ($berkasIds as $id) {
                    $berkas = Berkas::find(trim($id));
                    
                    // Pastikan berkas ada dan sedang dipegang oleh pengirim
                    if(!$berkas || $berkas->posisi_sekarang_user_id !== $pengirim->id) {
                        continue; 
                    }

                    // Update Status Berkas
                    $berkas->status_pengiriman = 'Dikirim';
                    $berkas->pengirim_id = $pengirim->id;
                    $berkas->penerima_id = $request->tujuan_user_id;
                    
                    // Trigger Argo Timer jika sesuai role & argo belum jalan
                    if (in_array($jabatanPengirim, $jabatanPemicuTimer) && is_null($berkas->waktu_mulai_proses)) {
                        $berkas->waktu_mulai_proses = now();
                    }

                    $berkas->save();

                    RiwayatBerkas::create([
                        'berkas_id' => $berkas->id,
                        'dari_user_id' => $pengirim->id,
                        'ke_user_id' => $request->tujuan_user_id,
                        'waktu_kirim' => now(),
                        'catatan_pengiriman' => $request->catatan_pengiriman,
                    ]);

                    $berkasDikirimCount++;
                }

                if ($berkasDikirimCount === 0) {
                    throw new \Exception('Tidak ada berkas valid yang dapat Anda kirim.');
                }
            });
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage() ?: 'Gagal mengirim berkas. Silakan coba lagi.');
        }

        return redirect()->route('ruang-kerja')->with('success', 'Berkas yang dipilih berhasil dikirim!');
    }

    public function terima(Berkas $berkas): RedirectResponse
    {
        if ($berkas->penerima_id !== Auth::id()) {
            return redirect()->back()->with('error', 'Anda tidak memiliki wewenang untuk menerima berkas ini.');
        }

        $berkas->status_pengiriman = 'Diterima';
        $berkas->posisi_sekarang_user_id = Auth::id();
        $berkas->pengirim_id = null; 
        $berkas->penerima_id = null;
        $berkas->save();

        return redirect()->route('ruang-kerja')->with('success', "Berkas {$berkas->nomer_berkas} berhasil diterima.");
    }
    
    public function tolak(Berkas $berkas): RedirectResponse
    {
        if ($berkas->penerima_id !== Auth::id()) {
            return redirect()->back()->with('error', 'Anda tidak memiliki wewenang untuk menolak berkas ini.');
        }

        $pengirimAsalId = $berkas->pengirim_id;
        $penolakSaatIniId = Auth::id();

        if (!$pengirimAsalId) {
            return redirect()->back()->with('error', 'Tidak dapat menolak berkas karena data pengirim asal tidak ditemukan.');
        }

        // Kembalikan berkas ke pengirim asal
        $berkas->penerima_id = $pengirimAsalId; 
        $berkas->pengirim_id = $penolakSaatIniId; 
        $berkas->status_pengiriman = 'Dikirim'; 
        $berkas->save();

        RiwayatBerkas::create([
            'berkas_id' => $berkas->id,
            'dari_user_id' => $penolakSaatIniId,
            'ke_user_id' => $pengirimAsalId,
            'waktu_kirim' => now(),
            'catatan_pengiriman' => 'Berkas ditolak dan dikembalikan.'
        ]);

        return redirect()->route('ruang-kerja')->with('success', "Berkas {$berkas->nomer_berkas} telah ditolak dan dikembalikan.");
    }

    public function selesaikan(Berkas $berkas)
    {
        $user = Auth::user();
        
        if (optional($user->jabatan)->nama_jabatan !== 'Petugas Loket Penyerahan') {
            return redirect()->back()->with('error', 'Hanya Petugas Loket Penyerahan yang dapat menyelesaikan berkas.');
        }

        if ($berkas->posisi_sekarang_user_id !== $user->id) {
            return redirect()->back()->with('error', 'Anda tidak memiliki wewenang untuk berkas ini.');
        }

        $berkas->status = 'Selesai';
        $berkas->waktu_selesai_proses = now();
        $berkas->save();

        RiwayatBerkas::create([
            'berkas_id' => $berkas->id,
            'dari_user_id' => $user->id,
            'ke_user_id' => $user->id,
            'waktu_kirim' => now(),
            'catatan_pengiriman' => 'Berkas telah diselesaikan oleh Petugas Loket Penyerahan.'
        ]);

        return redirect()->route('ruang-kerja')->with('success', 'Berkas berhasil diselesaikan!');
    }

    public function tutup(Request $request, Berkas $berkas): RedirectResponse
    {
        $request->validate(['catatan_aksi' => 'required|string|max:255']);
        
        if ($berkas->posisi_sekarang_user_id !== Auth::id()) {
            return redirect()->back()->with('error', 'Anda tidak memiliki wewenang untuk berkas ini.');
        }

        $berkas->status = 'Ditutup';
        $berkas->save();

        RiwayatBerkas::create([
            'berkas_id' => $berkas->id,
            'dari_user_id' => Auth::id(),
            'ke_user_id' => Auth::id(),
            'waktu_kirim' => now(),
            'catatan_pengiriman' => 'Berkas Ditutup. Catatan: ' . $request->catatan_aksi,
        ]);

        return redirect()->route('ruang-kerja')->with('success', "Berkas {$berkas->nomer_berkas} telah ditutup.");
    }

    public function pending(Request $request, Berkas $berkas): RedirectResponse
    {
        $request->validate(['catatan_aksi' => 'required|string|max:255']);
        
        if ($berkas->posisi_sekarang_user_id !== Auth::id()) {
            return redirect()->back()->with('error', 'Anda tidak memiliki wewenang untuk berkas ini.');
        }

        $berkas->status = 'Pending';
        $berkas->save();

        RiwayatBerkas::create([
            'berkas_id' => $berkas->id,
            'dari_user_id' => Auth::id(),
            'ke_user_id' => Auth::id(),
            'waktu_kirim' => now(),
            'catatan_pengiriman' => 'Berkas Ditunda (Pending). Catatan: ' . $request->catatan_aksi,
        ]);

        return redirect()->route('ruang-kerja')->with('success', "Berkas {$berkas->nomer_berkas} telah ditunda.");
    }
    
    public function aktifkan(Berkas $berkas): RedirectResponse
    {
        if ($berkas->posisi_sekarang_user_id !== Auth::id()) {
            return redirect()->back()->with('error', 'Anda tidak memiliki wewenang untuk berkas ini.');
        }

        $berkas->status = 'Diproses';
        $berkas->save();

        RiwayatBerkas::create([
            'berkas_id' => $berkas->id,
            'dari_user_id' => Auth::id(),
            'ke_user_id' => Auth::id(),
            'waktu_kirim' => now(),
            'catatan_pengiriman' => 'Berkas diaktifkan kembali dari status Pending.',
        ]);

        return redirect()->route('ruang-kerja')->with('success', "Berkas {$berkas->nomer_berkas} telah diaktifkan kembali.");
    }

    /**
     * Menangani Aksi Update Status Khusus (Pengumuman, Berkas Kembali, Selesai) dari Modal.
     */
    public function updateStatusKhusus(Request $request, Berkas $berkas): RedirectResponse
    {
        $request->validate([
            'status' => 'required|string|max:255',
            'keterangan' => 'required|string',
            'hari_pengumuman' => 'nullable|integer|min:1',
        ]);

        if ($berkas->posisi_sekarang_user_id !== Auth::id()) {
            return redirect()->back()->with('error', 'Anda tidak memiliki wewenang untuk memperbarui status berkas ini.');
        }

        $statusLama = $berkas->status;
        $berkas->status = $request->status;

        $tambahanKeterangan = '';

        if ($request->filled('hari_pengumuman')) {
            $tambahanKeterangan = " (Menunggu waktu: {$request->hari_pengumuman} Hari).";
        } 
        
        if (strtolower($request->status) === 'selesai') {
            $berkas->waktu_selesai_proses = now();
        }

        $berkas->save();

        RiwayatBerkas::create([
            'berkas_id' => $berkas->id,
            'dari_user_id' => Auth::id(),
            'ke_user_id' => Auth::id(),
            'waktu_kirim' => now(),
            'catatan_pengiriman' => 'Status diubah dari [' . $statusLama . '] menjadi [' . $request->status . ']' . $tambahanKeterangan . ' Catatan: ' . $request->keterangan,
        ]);

        return redirect()->back()->with('success', 'Status berkas berhasil diperbarui menjadi: ' . $request->status);
    }

    public function storeKuasaAjax(Request $request)
    {
        $request->validate([
            'kode_kuasa_baru' => 'required|string|unique:penerima_kuasas,kode_kuasa|max:50',
            'nama_kuasa_baru' => 'required|string|max:255',
            'nomer_wa_baru'   => 'required|string|max:20',
        ]);

        try {
            $kuasa = PenerimaKuasa::create([
                'kode_kuasa' => $request->kode_kuasa_baru,
                'nama_kuasa' => $request->nama_kuasa_baru,
                'nomer_wa'   => $request->nomer_wa_baru,
            ]);

            return response()->json([
                'success' => true,
                'data' => $kuasa,
                'message' => 'Penerima Kuasa berhasil ditambahkan'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage()
            ], 500);
        }
    }
}