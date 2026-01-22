<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\WaLog;
use App\Models\WaTemplate;
use App\Models\WaPlaceholder;
use App\Models\Berkas; 
use Illuminate\Support\Str; 
use Exception;

class WaService
{
    protected $baseUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->baseUrl = env('WA_API_URL', 'http://192.168.100.15:3000'); 
        $this->apiKey = env('WA_API_KEY', '');
    }

    public function sendByTemplate($templateName, $targetPhone, $dataBerkas = [], $userId = null)
    {
        // 1. Cari Template
        $template = WaTemplate::where('nama_template', $templateName)->first();
        if (!$template) {
            Log::error("WA Error: Template '$templateName' tidak ditemukan.");
            return ['status' => false, 'message' => 'Template tidak ditemukan'];
        }

        // 2. SIAPKAN DATA BERKAS (LOAD OTOMATIS SEMUA RELASI)
        $berkas = $this->prepareBerkasData($dataBerkas);

        if (!$berkas) {
            return $this->send($targetPhone, $template->isi_pesan, null, $userId, $template->id);
        }

        // 3. Parse Placeholder (Ganti kode {..} dengan data asli)
        $message = $this->parseTemplate($template->isi_pesan, $berkas);
        
        // 4. Kirim Pesan
        return $this->send($targetPhone, $message, $berkas->id, $userId, $template->id);
    }

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

            $this->logMessage($number, $message, $status, $responseData['message'] ?? $response->body(), $berkasId, $userId, $templateId);

            return $responseData;

        } catch (Exception $e) {
            Log::error("WA Exception to {$number}: " . $e->getMessage());
            $this->logMessage($number, $message, 'failed', "Koneksi Error: " . $e->getMessage(), $berkasId, $userId, $templateId);
            return ['status' => false, 'message' => 'Gagal koneksi ke Server WA'];
        }
    }

    // =========================================================================
    // LOGIC INTI (SMART LOADER)
    // =========================================================================

    protected function prepareBerkasData($data)
    {
        // Ambil ID
        $id = null;
        if ($data instanceof Berkas) $id = $data->id;
        elseif (is_array($data)) $id = $data['id'] ?? ($data['berkas_id'] ?? null);
        elseif (is_numeric($data)) $id = $data;

        if (!$id) return null;

        // Daftar kemungkinan nama relasi (Snake Case & Camel Case)
        // Kita cek satu-satu mana yang ada di Model Berkas
        $potentialRelations = [
            'jenisPermohonan', 'jenis_permohonan', // Cek keduanya
            'desa', 
            'kecamatan',
            'user', 'petugas', // User penginput
            'petugasUkur', 'petugas_ukur',
            'penerimaKuasa', 'penerima_kuasa',
            'riwayatBerkas', 'riwayat_berkas'
        ];

        $validRelations = [];
        $dummyModel = new Berkas();

        foreach ($potentialRelations as $rel) {
            if (method_exists($dummyModel, $rel)) {
                $validRelations[] = $rel;
            }
        }

        // Load Berkas dengan relasi yang VALID saja
        return Berkas::with($validRelations)->find($id);
    }

    protected function parseTemplate($message, $data)
    {
        $placeholders = WaPlaceholder::all();
        
        foreach ($placeholders as $p) {
            $key = $p->placeholder; // Contoh: {jenis_permohonan}
            $fieldRaw = trim($p->deskripsi); // Contoh: jenis_permohonan.nama_jenis

            // Strategi Pencarian Data (Coba berbagai variasi penulisan)
            $value = null;

            // 1. Coba ambil persis sesuai database
            $value = data_get($data, $fieldRaw);

            // 2. Jika gagal & ada titik (relasi), coba ubah bagian depan ke camelCase
            // (Misal DB tulis 'jenis_permohonan.nama', tapi relasi aslinya 'jenisPermohonan')
            if (is_null($value) && str_contains($fieldRaw, '.')) {
                $parts = explode('.', $fieldRaw, 2);
                $camelRelation = Str::camel($parts[0]); // jenis_permohonan -> jenisPermohonan
                $value = data_get($data, "$camelRelation.{$parts[1]}");
            }

            // 3. Jika masih gagal, coba ubah bagian depan ke snake_case
            // (Misal DB tulis 'jenisPermohonan.nama', tapi relasi aslinya 'jenis_permohonan')
            if (is_null($value) && str_contains($fieldRaw, '.')) {
                $parts = explode('.', $fieldRaw, 2);
                $snakeRelation = Str::snake($parts[0]); // jenisPermohonan -> jenis_permohonan
                $value = data_get($data, "$snakeRelation.{$parts[1]}");
            }

            // 4. Bersihkan hasil (jika object/array, kosongkan agar tidak error)
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