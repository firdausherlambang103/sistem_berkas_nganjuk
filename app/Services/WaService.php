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
        // Pastikan Anda sudah mengatur ini di .env
        $this->baseUrl = env('WA_API_URL', 'http://localhost:3000'); 
        $this->apiKey = env('WA_API_KEY', '');
    }

    /**
     * Mengirim pesan WA (Text only atau dengan Template)
     */
    public function send($number, $message, $berkasId = null, $userId = null)
    {
        try {
            // Format nomor (hapus karakter non-digit, ganti 08/628 dengan 628)
            $number = $this->formatNumber($number);

            $response = Http::post("{$this->baseUrl}/send-message", [
                'number' => $number,
                'message' => $message,
                'api_key' => $this->apiKey
            ]);

            $status = $response->successful() ? 'success' : 'failed';
            $responseData = $response->json();

            // Simpan Log
            $this->logMessage($number, $message, $status, $responseData['message'] ?? $response->body(), $berkasId, $userId);

            return $responseData;

        } catch (Exception $e) {
            Log::error("WA Error: " . $e->getMessage());
            $this->logMessage($number, $message, 'failed', $e->getMessage(), $berkasId, $userId);
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Mengirim pesan berdasarkan Template
     */
    public function sendByTemplate($templateName, $number, $data = [], $userId = null)
    {
        $template = WaTemplate::where('nama_template', $templateName)->first();

        if (!$template) {
            return ['status' => false, 'message' => 'Template tidak ditemukan'];
        }

        $message = $this->parseTemplate($template->isi_pesan, $data);
        
        // Ambil berkas_id dari data jika ada
        $berkasId = $data['berkas_id'] ?? null;

        return $this->send($number, $message, $berkasId, $userId);
    }

    /**
     * Mengganti Placeholder (misal: [NAMA_PEMOHON]) dengan data asli
     */
    protected function parseTemplate($message, $data)
    {
        $placeholders = WaPlaceholder::all();

        foreach ($placeholders as $placeholder) {
            $key = $placeholder->placeholder; // misal: [NAMA]
            $field = $placeholder->deskripsi; // misal: nama_pemohon (sesuaikan mapping di DB)

            if (isset($data[$field])) {
                $message = str_replace($key, $data[$field], $message);
            } elseif (isset($data[strtolower($key)])) {
                 // Fallback cek key array lowercase tanpa kurung siku
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
        // Tambahkan @c.us untuk format group/personal WA Web JS jika perlu
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
            'error_message' => $error,
            'berkas_id' => $berkasId,
            'user_id' => $userId ?? auth()->id(),
        ]);
    }
}