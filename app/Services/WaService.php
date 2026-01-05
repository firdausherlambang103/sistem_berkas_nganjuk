<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WaService
{
    /**
     * Mengirim pesan ke WA Server (Node.js)
     * * @param string $target Nomor tujuan (08xxx atau 62xxx)
     * @param string $message Isi pesan
     * @return array ['status' => bool, 'detail' => string]
     */
    public static function send($target, $message)
    {
        // -----------------------------------------------------------
        // KONFIGURASI URL SERVER WA
        // -----------------------------------------------------------
        // Jika Node.js jalan di komputer yang sama: http://localhost:3000/send-message
        // Jika Node.js jalan di komputer lain (LAN): Ganti localhost dengan IP (misal: 192.168.1.10)
        $url = 'http://192.168.100.15:3000/send-message';
        // -----------------------------------------------------------

        try {
            // 1. Bersihkan Format Nomor HP
            $target = preg_replace('/[^0-9]/', '', $target); // Hapus spasi, strip, dll
            
            // Ubah 08xx menjadi 628xx
            if (substr($target, 0, 1) == '0') {
                $target = '62' . substr($target, 1);
            }

            // 2. Kirim Request POST ke Node.js Server
            // Timeout 15 detik agar tidak loading selamanya jika server mati
            $response = Http::timeout(15)->post($url, [
                'number' => $target,
                'message' => $message,
            ]);

            // 3. Ambil Respon JSON
            $body = $response->json();

            // 4. Cek Status Pengiriman
            if ($response->successful() && isset($body['status']) && $body['status'] == true) {
                return [
                    'status' => true,
                    'detail' => 'Pesan berhasil dikirim via WA Server.'
                ];
            } else {
                // Jika server WA merespon error (misal: nomor tidak terdaftar / belum scan QR)
                $pesanError = $body['message'] ?? 'Gagal mengirim pesan (Error tidak diketahui).';
                
                // Catat error ke Log Laravel (storage/logs/laravel.log)
                Log::error("WA Server Gagal Kirim ke $target: " . $pesanError);

                return [
                    'status' => false,
                    'detail' => $pesanError
                ];
            }

        } catch (\Exception $e) {
            // Jika tidak bisa konek ke Node.js sama sekali (Server Mati)
            Log::error('WA Service Exception: ' . $e->getMessage());
            
            return [
                'status' => false,
                'detail' => 'Koneksi ke Server WA Gagal. Pastikan "node app.js" sudah dijalankan.'
            ];
        }
    }
}