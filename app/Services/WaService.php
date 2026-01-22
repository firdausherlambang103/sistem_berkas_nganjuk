<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\WaLog;
use App\Models\WaTemplate;
use App\Models\WaPlaceholder;
use App\Models\Berkas; // Pastikan Model Berkas di-import
use Exception;

class WaService
{
    protected $baseUrl;
    protected $apiKey;

    public function __construct()
    {
        // Sesuaikan URL Node.js Anda
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

        // 2. OLAH DATA BERKAS (KUNCI PERBAIKAN)
        // Kita pastikan dataBerkas memuat semua relasi agar placeholder {jenis_permohonan} dsb bisa tampil.
        $berkas = $this->prepareBerkasData($dataBerkas);

        if (!$berkas) {
            return ['status' => false, 'message' => 'Data berkas tidak valid'];
        }

        // 3. Parse Placeholder
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

            // Log ke Database
            $this->logMessage($number, $message, $status, $responseData['message'] ?? $response->body(), $berkasId, $userId, $templateId);

            return $responseData;

        } catch (Exception $e) {
            Log::error("WA Exception to {$number}: " . $e->getMessage());
            
            // Log Error
            $this->logMessage($number, $message, 'failed', "Koneksi Error: " . $e->getMessage(), $berkasId, $userId, $templateId);
            
            return ['status' => false, 'message' => 'Gagal koneksi ke Server WA'];
        }
    }

    // =========================================================================
    // HELPER FUNCTIONS (LOGIC INTI)
    // =========================================================================

    /**
     * Memastikan Data Berkas memuat Relasi (Eager Loading)
     * Ini menyamakan logika dengan aplikasi-berkas agar data relasi tampil.
     */
    protected function prepareBerkasData($data)
    {
        // Ambil ID dari data (bisa berupa object Model, Array, atau Integer ID)
        $id = null;
        if ($data instanceof Berkas) {
            $id = $data->id;
        } elseif (is_array($data)) {
            $id = $data['id'] ?? ($data['berkas_id'] ?? null);
        } elseif (is_numeric($data)) {
            $id = $data;
        }

        if (!$id) return null;

        // LOAD ULANG BERKAS DENGAN SEMUA RELASI
        // Sesuaikan nama fungsi relasi di Model Berkas Anda (camelCase)
        return Berkas::with([
            'jenisPermohonan', // relasi ke jenis_permohonans
            'desa',            // relasi ke desas
            'kecamatan',       // relasi ke kecamatans
            'user',            // relasi ke users (petugas loket/penginput)
            'petugasUkur',     // relasi ke petugas_ukur
            'penerimaKuasa'    // relasi ke penerima_kuasas
        ])->find($id);
    }

    /**
     * Mengganti {placeholder} dengan data asli
     */
    protected function parseTemplate($message, $data)
    {
        $placeholders = WaPlaceholder::all();
        
        foreach ($placeholders as $p) {
            $key = $p->placeholder; // contoh: {nama_pemohon}
            $field = $p->deskripsi; // contoh: jenisPermohonan.nama_jenis

            // Gunakan data_get untuk mengambil data relasi (support dot notation)
            // Contoh: $data->jenisPermohonan->nama_jenis
            $value = data_get($data, $field);

            // Cek jika value kosong, set string kosong agar tidak tampil {placeholder} mentah
            if (is_null($value)) {
                $value = ''; 
            } elseif (is_object($value) || is_array($value)) {
                // Jika hasil masih berupa object (karena salah set field), kosongkan
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

    protected function logMessage($number, $message, $status, $keterangan, $berkasId, $userId, $templateId)
    {
        WaLog::create([
            'target_phone' => $number,
            'pesan' => $message,
            'status' => $status,
            'keterangan' => substr((string)$keterangan, 0, 255),
            'berkas_id' => $berkasId,
            'user_id' => $userId ?? auth()->id(),
            'template_id' => $templateId
        ]);
    }
}