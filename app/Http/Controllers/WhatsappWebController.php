<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Berkas;
use App\Models\WaTemplate;
use App\Models\WaLog;
use App\Services\WaService;

class WhatsappWebController extends Controller
{
    public function send(Request $request)
    {
        // Inisialisasi variabel agar tidak error 'Undefined variable'
        $pesan = '-'; 
        
        // 1. Validasi Input
        $request->validate([
            'berkas_id'    => 'required|exists:berkas,id',
            'template_id'  => 'required|exists:wa_templates,id',
            'nomer_tujuan' => 'required',
        ]);

        try {
            // Load relasi yang mungkin dibutuhkan untuk placeholder
            // Pastikan relasi 'kecamatan', 'desa' ada di model Berkas atau sesuaikan nama relasinya
            $berkas = Berkas::with(['pengirim', 'jenisPermohonan', 'kecamatan', 'desa', 'penerimaKuasa'])
                            ->findOrFail($request->berkas_id);
            
            $template = WaTemplate::findOrFail($request->template_id);

            $pesan = $template->pesan;

            // 2. Ganti Placeholder Pesan (Sistem Dinamis)
            // -----------------------------------------------------------
            // Daftar pemetaan Kode -> Data Asli
            // Anda bisa menambahkan placeholder lain di sini sesuai kebutuhan
            $replacements = [
                // Info Dasar
                '{nama_pemohon}'    => $berkas->nama_pemohon,
                '{nomer_berkas}'    => $berkas->nomer_berkas,
                '{status}'          => $berkas->status,
                '{nomer_hak}'       => $berkas->nomer_hak ?? '-',
                '{jenis_alas_hak}'  => $berkas->jenis_alas_hak ?? '-',
                
                // Info Wilayah (Handling jika data berupa objek/string)
                '{desa}'            => $berkas->desa, 
                '{kecamatan}'       => $berkas->kecamatan, 
                
                // Info Tambahan
                '{jenis_permohonan}' => $berkas->jenisPermohonan->nama_permohonan ?? '-',
                '{tanggal_masuk}'    => $berkas->created_at->format('d-m-Y'),
                '{waktu_masuk}'      => $berkas->created_at->format('H:i'),
                '{petugas}'          => Auth::user()->name,
                
                // Info Kuasa (Opsional)
                '{penerima_kuasa}'   => $berkas->penerimaKuasa->nama ?? '-',
            ];

            // Lakukan replace semua placeholder yang ditemukan
            foreach ($replacements as $key => $value) {
                // Pastikan value string, jika null ubah jadi string kosong
                $pesan = str_replace($key, (string) ($value ?? ''), $pesan);
            }
            // -----------------------------------------------------------

            // 3. Kirim via WaService (Node.js)
            $result = WaService::send($request->nomer_tujuan, $pesan);

            // 4. Simpan Log ke Database
            WaLog::create([
                'berkas_id'    => $berkas->id,
                'user_id'      => Auth::id(),
                'template_id'  => $template->id,
                'target_phone' => $request->nomer_tujuan,
                'pesan'        => $pesan, // Pesan yang sudah di-replace
                'status'       => $result['status'] ? 'Sukses' : 'Gagal',
                'keterangan'   => $result['detail'] ?? '-'
            ]);

            // 5. Respon ke Browser
            if ($result['status']) {
                return response()->json(['success' => true, 'message' => 'Pesan berhasil dikirim!']);
            } else {
                return response()->json(['success' => false, 'message' => 'Gagal: ' . ($result['detail'] ?? 'Unknown Error')]);
            }

        } catch (\Exception $e) {
            // Jika error terjadi, kita tetap coba simpan log gagal
            try {
                if(isset($request->berkas_id)) {
                    WaLog::create([
                        'berkas_id'    => $request->berkas_id,
                        'user_id'      => Auth::id(),
                        'template_id'  => $request->template_id ?? null,
                        'target_phone' => $request->nomer_tujuan,
                        'pesan'        => $pesan, 
                        'status'       => 'Error Sistem',
                        'keterangan'   => $e->getMessage()
                    ]);
                }
            } catch (\Exception $ex) {
                // Abaikan jika log gagal disimpan (misal DB mati)
            }

            return response()->json(['success' => false, 'message' => 'Error Sistem: ' . $e->getMessage()]);
        }
    }
}