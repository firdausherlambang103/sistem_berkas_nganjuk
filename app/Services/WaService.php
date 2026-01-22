<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\WaLog;
use App\Models\WaTemplate;
use App\Models\WaPlaceholder;
use App\Models\Berkas; // Import Model Berkas
use Illuminate\Support\Str; // Import Str untuk manipulasi string
use Exception;

class WaService
{
    protected $baseUrl;
    protected $apiKey;

    public function __construct()
    {
        // IP Server WA (Default 192.168.100.15)
        $this->baseUrl = env('WA_API_URL', 'http://192.168.100.15:3000'); 
        $this->apiKey = env('WA_API_KEY', '');
    }

    /**
     * Kirim Pesan berdasarkan Template (Fungsi Utama)
     */
    public function sendByTemplate($templateName, $targetPhone, $dataBerkas = [], $userId = null)
    {
        // 1. Cari Template
        $template = WaTemplate::where('nama_template', $templateName)->first();
        if (!$template) {
            Log::error("WA Error: Template '$templateName' tidak ditemukan.");
            return ['status' => false, 'message' => 'Template tidak ditemukan'];
        }

        // 2. SIAPKAN DATA BERKAS (Load Semua Relasi)
        $berkas = $this->prepareBerkasData($dataBerkas);

        if (!$berkas) {
            // Jika data tidak valid, kirim pesan raw tanpa replace (fallback)
            return $this->send($targetPhone, $template->isi_pesan, null, $userId, $template->id);
        }

        // 3. Parse Placeholder dengan Smart Detection
        $message = $this->parseTemplate($template->isi_pesan, $berkas);
        
        // 4. Kirim Pesan
        return $this->send($targetPhone, $message, $berkas->id, $userId, $template->id);
    }

    /**
     * Mengirim pesan Raw ke API WA
     */
    public function send($number, $message, $berkasId = null, $userId = null, $templateId = null)
    {
        try {
            $number = $this->formatNumber($number);

            $response = Http::timeout(15)->post("{$this->baseUrl}/send-message", [
                'number' => $number,
                'message' => $message,
                'api_key' => $this->apiKey
            ]);

            $responseData = $response->json();
            $status = ($response->successful() && isset($responseData['status']) && $responseData['status']) ? 'success' : 'failed';

            // Log ke Database (Sesuai struktur db_1.sql)
            $this->logMessage($number, $message, $status, $responseData['message'] ?? $response->body(), $berkasId, $userId, $templateId);

            return $responseData;

        } catch (Exception $e) {
            Log::error("WA Exception to {$number}: " . $e->getMessage());
            
            $this->logMessage($number, $message, 'failed', "Koneksi Error: " . $e->getMessage(), $berkasId, $userId, $templateId);
            
            return ['status' => false, 'message' => 'Gagal koneksi ke Server WA'];
        }
    }

    // =========================================================================
    // LOGIC PERBAIKAN (SMART RELATION LOADER)
    // =========================================================================

    /**
     * Memastikan Data Berkas memuat Relasi
     */
    protected function prepareBerkasData($data)
    {
        $id = null;
        if ($data instanceof Berkas) {
            $id = $data->id;
        } elseif (is_array($data)) {
            $id = $data['id'] ?? ($data['berkas_id'] ?? null);
        } elseif (is_numeric($data)) {
            $id = $data;
        }

        if (!$id) return null;

        // LOAD ULANG dengan semua relasi yang mungkin ada di Model Berkas
        return Berkas::with([
            'jenisPermohonan', // Pastikan nama method di Model Berkas benar (camelCase)
            'desa',            
            'kecamatan',       
            'user',            
            'petugasUkur',
            'penerimaKuasa'
        ])->find($id);
    }

    /**
     * Mengganti {placeholder} dengan data asli (Support Snake Case & Camel Case)
     */
    protected function parseTemplate($message, $data)
    {
        $placeholders = WaPlaceholder::all();
        
        foreach ($placeholders as $p) {
            $key = $p->placeholder; // contoh: {jenis_permohonan}
            $field = trim($p->deskripsi); // contoh: jenis_permohonan.nama_jenis

            // 1. Coba ambil langsung (persis seperti di database)
            $value = data_get($data, $field);

            // 2. Jika GAGAL & mengandung titik (relasi), coba konversi ke camelCase
            // Contoh: DB tulis 'jenis_permohonan.nama', tapi Model pakai 'jenisPermohonan'
            if (is_null($value) && str_contains($field, '.')) {
                $parts = explode('.', $field);
                if (count($parts) == 2) {
                    $relationName = Str::camel($parts[0]); // ubah jenis_permohonan -> jenisPermohonan
                    $attributeName = $parts[1];
                    $value = data_get($data, "$relationName.$attributeName");
                }
            }

            // 3. Bersihkan Value (jika null/object, jadikan string kosong)
            if (is_null($value) || is_array($value) || is_object($value)) {
                $value = ''; 
            }

            $message = str_replace($key, (string)$value, $message);
        }

        return $message;
    }

    protected function formatNumber($number)
    {
        $number = preg_replace('/[^0-9]/', '', $number);
        if (substr($number, 0, 1) == '0') $number = '62' . substr($number, 1);
        if (substr($number, 0, 2) != '62') $number = '62' . $number;
        if (!str_ends_with($number, '@c.us')) $number .= '@c.us';
        return $number;
    }

    // LOG Sesuai Kolom Database db_1.sql
    protected function logMessage($number, $message, $status, $keterangan, $berkasId, $userId, $templateId)
    {
        WaLog::create([
            'target_phone' => $number, // DB: target_phone
            'pesan' => $message,
            'status' => $status,
            'keterangan' => substr((string)$keterangan, 0, 255), // DB: keterangan
            'berkas_id' => $berkasId,
            'user_id' => $userId ?? auth()->id(),
            'template_id' => $templateId
        ]);
    }
}