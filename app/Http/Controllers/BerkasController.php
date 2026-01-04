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
        return view('berkas.create', compact('kecamatans', 'jenisPermohonans', 'penerimaKuasas'));
    }

    /**
     * Menyimpan berkas baru dan mengarahkan ke alur penjadwalan.
     */
    public function store(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'nomer_berkas' => 'required|string|max:255|unique:berkas,nomer_berkas',
            'nama_pemohon' => 'required|string|max:255',
            'jenis_alas_hak' => 'required|string|max:255',
            'nomer_hak' => 'required|string|max:255',
            'jenis_permohonan_id' => 'required|exists:jenis_permohonans,id',
            'kecamatan' => 'required|string|max:255',
            'desa' => 'required|string|max:255',
            'nomer_wa' => 'nullable|string|max:20',
            'penerima_kuasa_id' => 'nullable|exists:penerima_kuasas,id',
            'catatan' => 'nullable|string',
        ]);

        $berkas = null; // Inisialisasi variabel berkas

        try {
            DB::transaction(function () use ($validatedData, &$berkas) {
                $currentUser = Auth::user();
                // Simpan instance berkas yang baru dibuat ke variabel $berkas
                $berkas = Berkas::create([
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
                    'posisi_sekarang_user_id' => $currentUser->id,
                    'status' => 'Diproses',
                    'status_pengiriman' => 'Diterima',
                    'pengirim_id' => $currentUser->id,
                    'waktu_mulai_proses' => now(),
                ]);
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

        // --- PERUBAHAN UTAMA DI SINI ---
        // Arahkan ke halaman pilih petugas dengan membawa ID berkas yang baru dibuat.
        return redirect()->route('jadwal-ukur.pilih-petugas', ['berkas' => $berkas->id])
                         ->with('success', 'Berkas baru berhasil dibuat! Silakan pilih petugas ukur.');
    }
    
    /**
     * Menampilkan detail dan riwayat berkas.
     */
    public function show(Berkas $berkas): View
    {
        $berkas->load('riwayat.dariUser.jabatan', 'riwayat.keUser.jabatan', 'posisiSekarang.jabatan', 'jenisPermohonan');
        return view('berkas.show', compact('berkas'));
    }

    /**
     * Menampilkan form untuk mengedit berkas (Hanya Admin).
     */
    public function edit(Berkas $berkas): View
    {
        $kecamatans = Kecamatan::orderBy('nama_kecamatan')->get();
        $jenisPermohonans = JenisPermohonan::orderBy('nama_permohonan')->get();
        return view('berkas.edit', compact('berkas', 'kecamatans', 'jenisPermohonans'));
    }
    
    /**
     * Memperbarui data berkas di database (Hanya Admin).
     */
    public function update(Request $request, Berkas $berkas): RedirectResponse
    {
        $validatedData = $request->validate([
            'nomer_berkas' => 'required|string|max:255|unique:berkas,nomer_berkas,' . $berkas->id,
            'nama_pemohon' => 'required|string|max:255',
            'jenis_alas_hak' => 'required|string|max:255',
            'nomer_hak' => 'required|string|max:255',
            'jenis_permohonan_id' => 'required|exists:jenis_permohonans,id',
            'kecamatan' => 'required|string|max:255',
            'desa' => 'required|string|max:255',
            'nomer_wa' => 'nullable|string|max:20',
            'catatan' => 'nullable|string',
        ]);
        $berkas->update($validatedData);
        return redirect()->route('dashboard')->with('success', 'Berkas berhasil diperbarui!');
    }

    /**
     * Menghapus berkas dari database (Hanya Admin).
     */
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
     * Memproses pengiriman berkas ke user lain (bisa satu atau banyak).
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
                $berkasDikirimCount = 0;

                foreach ($berkasIds as $id) {
                    $berkas = Berkas::find(trim($id));
                    if(!$berkas || $berkas->posisi_sekarang_user_id !== $pengirim->id) {
                        continue; 
                    }
                    $berkas->status_pengiriman = 'Dikirim';
                    $berkas->pengirim_id = $pengirim->id;
                    $berkas->penerima_id = $request->tujuan_user_id;
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
                    throw new \Exception('Tidak ada berkas yang dapat Anda kirim.');
                }
            });
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage() ?: 'Gagal mengirim berkas. Silakan coba lagi.');
        }
        return redirect()->route('ruang-kerja')->with('success', 'Berkas yang dipilih berhasil dikirim!');
    }

    /**
     * Memproses penerimaan berkas.
     */
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
    
    /**
     * Memproses penolakan berkas dan mengembalikannya ke pengirim.
     */
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

    /**
     * Menyelesaikan proses berkas.
     */
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

    /**
     * Mengubah status berkas menjadi Ditutup. Memerlukan 'catatan_aksi'.
     */
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

    /**
     * Mengubah status berkas menjadi Ditunda (Pending). Memerlukan 'catatan_aksi'.
     */
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
    
    /**
     * Mengaktifkan kembali berkas dari status 'Pending'.
     */
    public function aktifkan(Berkas $berkas): RedirectResponse
    {
        if ($berkas->posisi_sekarang_user_id !== Auth::id()) {
            return redirect()->back()->with('error', 'Anda tidak memiliki wewenang untuk berkas ini.');
        }
        $berkas->status = 'Diproses';
        if (is_null($berkas->waktu_mulai_proses)) {
            $berkas->waktu_mulai_proses = now();
        }
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

    public function storeKuasaAjax(Request $request)
    {
        $request->validate([
            'kode_kuasa_baru' => 'required|string|unique:penerima_kuasas,kode_kuasa|max:50',
            'nama_kuasa_baru' => 'required|string|max:255',
            'nomer_wa_baru'   => 'required|string|max:20',
        ]);

        $kuasa = PenerimaKuasa::create([
            'kode_kuasa' => $request->kode_kuasa_baru,
            'nama_kuasa' => $request->nama_kuasa_baru,
            'nomer_wa'   => $request->nomer_wa_baru,
        ]);

        return response()->json([
            'success' => true,
            'data' => $kuasa
        ]);
    }
}

