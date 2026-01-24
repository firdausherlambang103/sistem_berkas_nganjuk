<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\WaLog;
use App\Models\WaTemplate;
use App\Models\WaPlaceholder;
use App\Models\Berkas;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;

class WaService
{
    protected $baseUrl;
    protected $apiKey;

    public function __construct()
    {
        // Pastikan Anda sudah mengatur WA_API_URL di file .env
        // Jika belum, default ke localhost port 3000
        $this->baseUrl = env('WA_API_URL', 'http://localhost:3000');
        $this->apiKey = env('WA_API_KEY', '');
    }

    /**
     * Kirim pesan langsung (Raw Message).
     * Method inilah yang dipanggil oleh WhatsappWebController.
     */
    public function send($number, $message, $berkasId = null, $userId = null, $templateId = null)
    {
        try {
            $number = $this->formatNumber($number);
            
            if (empty($number)) {
                return ['status' => false, 'message' => 'Nomor tujuan tidak valid/kosong'];
            }

            // Kirim Request ke WA Gateway (Node.js / Vendor Lain)
            $response = Http::timeout(15)->post("{$this->baseUrl}/send-message", [
                'number' => $number,
                'message' => $message,
                'api_key' => $this->apiKey
            ]);
            
            $responseData = $response->json();
            
            // Cek status respon dari gateway
            // Sesuaikan key 'status' ini dengan respon asli gateway Anda
            $status = ($response->successful() && isset($responseData['status']) && $responseData['status']) ? 'Sukses' : 'Gagal';
            $keterangan = $responseData['message'] ?? $response->body();

            // Simpan Log
            $this->logMessage($number, $message, $status, $keterangan, $berkasId, $userId, $templateId);
            
            return [
                'status' => $status === 'Sukses',
                'message' => $keterangan
            ];

        } catch (Exception $e) {
            Log::error("WA Exception to {$number}: " . $e->getMessage());
            $this->logMessage($number, $message, 'Gagal', "Koneksi Error: " . $e->getMessage(), $berkasId, $userId, $templateId);
            return ['status' => false, 'message' => 'Gagal koneksi ke Server WA'];
        }
    }

    /**
     * Kirim pesan berdasarkan Template (Untuk penggunaan internal / otomatis)
     */
    public function sendByTemplate($templateName, $targetPhone, $dataBerkas = [], $userId = null)
    {
        // [PENTING] Gunakan kolom 'nama' (bukan nama_template)
        $template = WaTemplate::where('nama', $templateName)->first();
        
        if (!$template) {
            Log::error("WA Error: Template '$templateName' tidak ditemukan.");
            return ['status' => false, 'message' => 'Template tidak ditemukan'];
        }

        // Siapkan Data
        $berkas = $this->prepareBerkasData($dataBerkas);
        
        // [PENTING] Gunakan kolom 'template' (bukan isi_pesan)
        $isiPesan = $template->template;

        // Parse Placeholder
        $message = $berkas ? $this->parseTemplate($isiPesan, $berkas) : $isiPesan;
        
        return $this->send($targetPhone, $message, $berkas ? $berkas->id : null, $userId, $template->id);
    }

    // --- Helper Methods ---

    protected function parseTemplate($message, $data)
    {
        $placeholders = WaPlaceholder::all();

        if ($placeholders->isEmpty()) return $message;

        foreach ($placeholders as $p) {
            $search = $p->placeholder; 
            $path = trim($p->deskripsi); 

            // 1. Normalisasi Path (Mapping Alias)
            if ($data instanceof Berkas) {
                if (Str::startsWith($path, 'desa.')) $path = Str::replaceFirst('desa.', 'dataDesa.', $path);
                if (Str::startsWith($path, 'kecamatan.')) $path = Str::replaceFirst('kecamatan.', 'dataKecamatan.', $path);
            }

            // 2. Ambil Data
            $value = data_get($data, $path);

            // 3. Smart Fallback (Jika relasi null, ambil string langsung)
            if ((is_null($value) || $value === '') && $data instanceof Berkas) {
                if (Str::contains(strtolower($path), 'desa') && !empty($data->desa)) {
                    $value = $data->desa; 
                } elseif (Str::contains(strtolower($path), 'kecamatan') && !empty($data->kecamatan)) {
                    $value = $data->kecamatan;
                } elseif (!str_contains($path, '.') && isset($data->$path)) {
                    $value = $data->$path;
                }
            }

            // 4. Format Tanggal
            if ($value instanceof \DateTime || $value instanceof Carbon) {
                $value = Carbon::parse($value)->format('d-m-Y');
            }

            // 5. Replace
            $message = str_replace($search, (string)($value ?? ''), $message);
        }

        return $message;
    }

    protected function prepareBerkasData($data)
    {
        $id = null;
        if ($data instanceof Berkas) $id = $data->id;
        elseif (is_numeric($data)) $id = $data;

        if (!$id) return null;

        // Load relasi penting
        return Berkas::with(['jenisPermohonan', 'dataDesa', 'dataKecamatan', 'posisiSekarang', 'penerimaKuasa'])->find($id);
    }

    protected function formatNumber($number)
    {
        $number = preg_replace('/[^0-9]/', '', $number);
        if (empty($number)) return '';
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

    // ... method send() dan sendByTemplate() yang sudah ada ...

    /**
     * Cek Status Koneksi ke WA Gateway
     */
    public function getStatus()
    {
        try {
            // Sesuaikan endpoint ini dengan dokumentasi WA Gateway Anda
            // Contoh umum: GET /status atau /session/status
            $response = Http::timeout(5)->get("{$this->baseUrl}/status"); 
            
            if ($response->successful()) {
                $data = $response->json();
                // Asumsi respon gateway: { status: 'CONNECTED' } atau { connected: true }
                $isConnected = isset($data['status']) && strtoupper($data['status']) === 'CONNECTED';
                
                return ['connected' => $isConnected, 'raw' => $data];
            }
        } catch (Exception $e) {
            // Silent fail
        }
        return ['connected' => false];
    }

    /**
     * Ambil QR Code dari Gateway
     */
    public function getQrCode()
    {
        try {
            // Endpoint umum: GET /qr atau /auth/qr
            // Pastikan gateway mengembalikan JSON { qr: "data:image/png;base64,..." }
            $response = Http::timeout(5)->get("{$this->baseUrl}/qr"); 
            
            if ($response->successful()) {
                return $response->json(); // Harusnya return ['qr_code' => 'base64string...']
            }
        } catch (Exception $e) {
            // Silent fail
        }
        return ['message' => 'Gagal mengambil QR. Pastikan server WA nyala.'];
    }

    public function logout()
    {
        try {
            Http::timeout(5)->post("{$this->baseUrl}/logout");
        } catch (Exception $e) { }
    }
}