<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\WaLog;
use App\Models\WaTemplate;
use App\Models\WaPlaceholder;
use Exception;

class WaService
{
    protected $baseUrl;
    protected $apiKey;

    public function __construct()
    {
        // PERBAIKAN: Default ke IP 192.168.100.15, bukan localhost
        $this->baseUrl = env('WA_API_URL', 'http://192.168.100.15:3000'); 
        $this->apiKey = env('WA_API_KEY', '');
    }

    public function send($number, $message, $berkasId = null, $userId = null)
    {
        try {
            $number = $this->formatNumber($number);

            // Request ke Node.js Server
            $response = Http::timeout(15)->post("{$this->baseUrl}/send-message", [
                'number' => $number,
                'message' => $message,
                'api_key' => $this->apiKey
            ]);

            $status = $response->successful() ? 'success' : 'failed';
            $responseData = $response->json();

            // Cek status dari API WA (misal: nomor tidak terdaftar)
            if (isset($responseData['status']) && !$responseData['status']) {
                $status = 'failed';
            }

            $this->logMessage($number, $message, $status, $responseData['message'] ?? $response->body(), $berkasId, $userId);

            return $responseData;

        } catch (Exception $e) {
            // Log error agar bisa dicek di storage/logs/laravel.log
            Log::error("WA Gagal Kirim ke {$number}: " . $e->getMessage());
            
            $this->logMessage($number, $message, 'failed', "Koneksi Gagal: " . $e->getMessage(), $berkasId, $userId);
            
            return [
                'status' => false, 
                'message' => 'Gagal terhubung ke Server WA (Pastikan server Node.js jalan di 192.168.100.15:3000)'
            ];
        }
    }

    // --- Helper Methods (Tidak Berubah) ---

    public function sendByTemplate($templateName, $number, $data = [], $userId = null)
    {
        $template = WaTemplate::where('nama_template', $templateName)->first();
        if (!$template) return ['status' => false, 'message' => 'Template tidak ditemukan'];

        $message = $this->parseTemplate($template->isi_pesan, $data);
        $berkasId = $data['berkas_id'] ?? null;

        return $this->send($number, $message, $berkasId, $userId);
    }

    protected function parseTemplate($message, $data)
    {
        $placeholders = WaPlaceholder::all();
        foreach ($placeholders as $placeholder) {
            $key = $placeholder->placeholder;
            $field = $placeholder->deskripsi;
            
            if (isset($data[$field])) {
                $message = str_replace($key, $data[$field], $message);
            } elseif (isset($data[strtolower($key)])) { // Fallback lowercase key
                 $message = str_replace($key, $data[strtolower($key)], $message);
            }
        }
        return $message;
    }

    protected function formatNumber($number)
    {
        $number = preg_replace('/[^0-9]/', '', $number);
        if (substr($number, 0, 1) == '0') {
            $number = '62' . substr($number, 1);
        }
        if (substr($number, 0, 2) != '62') {
            $number = '62' . $number;
        }
        if (!str_ends_with($number, '@c.us')) {
            $number .= '@c.us';
        }
        return $number;
    }

    protected function logMessage($number, $message, $status, $error = null, $berkasId = null, $userId = null)
    {
        WaLog::create([
            'tujuan' => $number,
            'pesan' => $message,
            'status' => $status,
            'error_message' => substr((string)$error, 0, 255),
            'berkas_id' => $berkasId,
            'user_id' => $userId ?? auth()->id(),
        ]);
    }
}