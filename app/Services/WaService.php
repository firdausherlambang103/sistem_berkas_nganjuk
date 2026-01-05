<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WaService
{
    /**
     * Mengirim pesan ke Local Node.js WA Server
     */
    public static function send($target, $message)
    {
        // URL Node.js Server kita (Port 3000)
        $url = 'http://localhost:3000/send-message';

        try {
            $response = Http::post($url, [
                'number' => $target,
                'message' => $message,
            ]);

            if ($response->successful() && $response['status'] == true) {
                return [
                    'status' => true,
                    'detail' => 'Terkirim via WA Server'
                ];
            } else {
                // Log error dari server nodejs
                Log::error('WA Server Error: ' . $response->body());
                return [
                    'status' => false,
                    'detail' => $response['message'] ?? 'Gagal koneksi ke WA Server'
                ];
            }

        } catch (\Exception $e) {
            Log::error('WA Service Exception: ' . $e->getMessage());
            return [
                'status' => false,
                'detail' => 'Server WA Mati/Tidak Terjangkau. Pastikan node app.js jalan.'
            ];
        }
    }
}