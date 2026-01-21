<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\WaTemplate; // Tambahkan Model
use App\Models\Berkas;     // Tambahkan Model

class WaService
{
    /**
     * Kirim pesan berdasarkan nama Template
     * * @param string $target Nomor WA Tujuan
     * @param string $templateName Nama template di database (case sensitive sesuai input admin)
     * @param Berkas $berkas Data berkas untuk mengisi placeholder
     */
    public static function sendFromTemplate($target, $templateName, Berkas $berkas)
    {
        // 1. Cek jika nomor kosong
        if (empty($target)) {
            return ['status' => false, 'detail' => 'Nomor tujuan kosong.'];
        }

        // 2. Ambil Template dari Database
        $template = WaTemplate::where('nama', $templateName)
                    ->where('status', 'aktif') // Hanya ambil yang aktif
                    ->first();

        if (!$template) {
            Log::warning("WA Template '$templateName' tidak ditemukan atau tidak aktif.");
            return ['status' => false, 'detail' => 'Template tidak ditemukan.'];
        }

        // 3. Proses Placeholder
        $message = self::parsePlaceholder($template->template, $berkas);

        // 4. Kirim Pesan
        return self::send($target, $message);
    }

    /**
     * Mengganti {placeholder} dengan data asli dari Berkas
     */
    private static function parsePlaceholder($message, Berkas $berkas)
    {
        // Daftar Mapping Placeholder -> Data Database
        // Pastikan key array ini sama dengan 'placeholder' di tabel wa_placeholders
        $replacements = [
            '{nomer_berkas}' => $berkas->nomer_berkas,
            '{nama_pemohon}' => $berkas->nama_pemohon,
            '{tahun}'        => $berkas->tahun,
            '{kecamatan}'    => $berkas->kecamatan,
            '{desa}'         => $berkas->desa,
            '{status}'       => $berkas->status,
            '{tanggal}'      => date('d-m-Y H:i'),
            '{posisi}'       => optional($berkas->posisiSekarangUser)->name ?? 'Sistem',
        ];

        // Lakukan replace string
        foreach ($replacements as $key => $value) {
            $message = str_replace($key, $value, $message);
        }

        return $message;
    }

    /**
     * Mengirim pesan ke WA Server (Fungsi Original Anda)
     */
    public static function send($target, $message)
    {
        // ... (Kode original Anda tetap sama di sini) ...
        $url = 'http://192.168.100.15:3000/send-message';
        
        try {
            $target = preg_replace('/[^0-9]/', '', $target);
            if (substr($target, 0, 1) == '0') {
                $target = '62' . substr($target, 1);
            }

            $response = Http::timeout(15)->post($url, [
                'number' => $target,
                'message' => $message,
            ]);

            $body = $response->json();

            if ($response->successful() && isset($body['status']) && $body['status'] == true) {
                return ['status' => true, 'detail' => 'Pesan berhasil dikirim via WA Server.'];
            } else {
                $pesanError = $body['message'] ?? 'Gagal mengirim pesan.';
                Log::error("WA Server Error: " . $pesanError);
                return ['status' => false, 'detail' => $pesanError];
            }
        } catch (\Exception $e) {
            Log::error('WA Service Exception: ' . $e->getMessage());
            return ['status' => false, 'detail' => 'Koneksi ke Server WA Gagal.'];
        }
    }
}