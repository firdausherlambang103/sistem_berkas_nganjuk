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
        // Pastikan URL ini sesuai dengan ip/port server WA Gateway (Node.js) Anda
        $this->baseUrl = env('WA_API_URL', 'http://127.0.0.1:3000');
        $this->apiKey = env('WA_API_KEY', '');
    }

    /**
     * ====================================================================
     * FUNGSI MANAJEMEN KONEKSI (Status, QR, Logout)
     * ====================================================================
     */

    public function getStatus()
    {
        try {
            $response = Http::timeout(3)->get("{$this->baseUrl}/status");
            
            if ($response->successful()) {
                $data = $response->json();
                $statusText = strtoupper($data['status'] ?? 'UNKNOWN');
                $connected = in_array($statusText, ['CONNECTED', 'READY', 'AUTHENTICATED', 'SUKSES', 'ONLINE']);
                
                return [
                    'connected' => $connected,
                    'status_text' => $statusText
                ];
            }
        } catch (Exception $e) {
            // Log::error("WA Status Check Gagal: " . $e->getMessage());
        }

        return ['connected' => false, 'status_text' => 'OFFLINE'];
    }

    public function getQrCode()
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/qr");
            
            if ($response->successful()) {
                $data = $response->json();
                return [
                    'qr_code' => $data['qr'] ?? $data['qr_code'] ?? null,
                    'message' => $data['message'] ?? 'Silakan scan QR Code'
                ];
            }
        } catch (Exception $e) {
            return ['message' => 'Gagal menghubungi server WA.'];
        }
        
        return ['message' => 'QR Code belum tersedia.'];
    }

    public function logout()
    {
        try {
            Http::timeout(5)->post("{$this->baseUrl}/logout");
        } catch (Exception $e) {
            Log::error("WA Logout Error: " . $e->getMessage());
        }
    }

    /**
     * ====================================================================
     * FUNGSI PENGIRIMAN PESAN & DOKUMEN
     * ====================================================================
     */

    /**
     * Kirim pesan menggunakan Template. Bisa otomatis jadi PDF jika ada fileUrl.
     * [DITAMBAHKAN] Parameter ke-5: $fileUrl (Opsional)
     */
    public function sendByTemplate($templateName, $targetPhone, $dataBerkas = [], $userId = null, $fileUrl = null)
    {
        $template = WaTemplate::where('nama', $templateName)->first();
        
        if (!$template) {
            Log::error("WA Error: Template '$templateName' tidak ditemukan.");
            return ['status' => false, 'message' => 'Template tidak ditemukan'];
        }

        $berkas = $this->prepareBerkasData($dataBerkas);
        $isiPesanRaw = $template->template;
        $message = $berkas ? $this->parseTemplate($isiPesanRaw, $berkas) : $isiPesanRaw;
        
        // Cek apakah ada File URL yang disisipkan
        if (!empty($fileUrl)) {
            // Jika ada, kirim sebagai Dokumen dengan pesan sebagai Caption
            return $this->sendPdf($targetPhone, $fileUrl, $message, $berkas ? $berkas->id : null, $userId, $template->id);
        }

        // Jika tidak ada file, kirim teks biasa
        return $this->send($targetPhone, $message, $berkas ? $berkas->id : null, $userId, $template->id);
    }

    /**
     * Fungsi dasar pengiriman teks biasa (Raw Send).
     */
    public function send($number, $message, $berkasId = null, $userId = null, $templateId = null)
    {
        try {
            $number = $this->formatNumber($number);
            if (empty($number)) return ['status' => false, 'message' => 'Nomor tujuan tidak valid'];

            $response = Http::timeout(15)->post("{$this->baseUrl}/send-message", [
                'number' => $number,
                'message' => $message,
                'api_key' => $this->apiKey 
            ]);
            
            $responseData = $response->json();
            $isSuccess = $response->successful() && (isset($responseData['status']) && $responseData['status'] == true);
            $statusLog = $isSuccess ? 'Sukses' : 'Gagal';
            $keterangan = $responseData['message'] ?? ($isSuccess ? 'Pesan terkirim' : 'Gagal kirim');

            $this->logMessage($number, $message, $statusLog, $keterangan, $berkasId, $userId, $templateId);
            
            return ['status' => $isSuccess, 'message' => $keterangan];

        } catch (Exception $e) {
            Log::error("WA Exception to {$number}: " . $e->getMessage());
            $this->logMessage($number, $message, 'Gagal', "Koneksi Gateway Error: " . $e->getMessage(), $berkasId, $userId, $templateId);
            return ['status' => false, 'message' => 'Gagal koneksi ke Server WA'];
        }
    }

    /**
     * [DITAMBAHKAN] Fungsi untuk mengirim File (PDF/Image)
     */
    public function sendPdf($number, $fileUrl, $caption = '', $berkasId = null, $userId = null, $templateId = null)
    {
        try {
            $number = $this->formatNumber($number);
            if (empty($number)) return ['status' => false, 'message' => 'Nomor tujuan tidak valid'];

            // Tembak ke endpoint /send-pdf di Node.js
            $response = Http::timeout(30)->post("{$this->baseUrl}/send-pdf", [
                'number' => $number,
                'file_url' => $fileUrl,
                'caption' => $caption,
                'api_key' => $this->apiKey
            ]);
            
            $responseData = $response->json();
            $isSuccess = $response->successful() && (isset($responseData['status']) && $responseData['status'] == true);
            $statusLog = $isSuccess ? 'Sukses' : 'Gagal';
            $keterangan = $responseData['message'] ?? ($isSuccess ? 'Dokumen terkirim' : 'Gagal kirim dokumen');

            // Log format agar kita tahu lampirannya apa
            $logPesan = "[LAMPIRAN DOKUMEN]\nURL: " . $fileUrl . "\n\n" . $caption;
            $this->logMessage($number, $logPesan, $statusLog, $keterangan, $berkasId, $userId, $templateId);
            
            return ['status' => $isSuccess, 'message' => $keterangan];

        } catch (Exception $e) {
            Log::error("WA PDF Exception to {$number}: " . $e->getMessage());
            $logPesan = "[LAMPIRAN DOKUMEN]\nURL: " . $fileUrl . "\n\n" . $caption;
            $this->logMessage($number, $logPesan, 'Gagal', "Koneksi Gateway Error: " . $e->getMessage(), $berkasId, $userId, $templateId);
            return ['status' => false, 'message' => 'Gagal koneksi ke Server WA'];
        }
    }

    /**
     * ====================================================================
     * HELPER METHODS (Private/Protected)
     * ====================================================================
     */

    protected function prepareBerkasData($data)
    {
        $id = null;
        if ($data instanceof Berkas) $id = $data->id;
        elseif (is_array($data)) $id = $data['id'] ?? ($data['berkas_id'] ?? null);
        elseif (is_numeric($data)) $id = $data;

        if (!$id) return null;

        $relations = ['jenisPermohonan', 'dataDesa', 'dataKecamatan', 'petugasUkur', 'penerimaKuasa', 'posisiSekarang', 'pengirim', 'user'];

        try {
            return Berkas::with($relations)->find($id);
        } catch (\Exception $e) {
            return Berkas::find($id);
        }
    }

    protected function parseTemplate($message, $data)
    {
        $placeholders = WaPlaceholder::all();
        if ($placeholders->isEmpty()) return $message;

        foreach ($placeholders as $p) {
            $search = $p->placeholder; 
            $path = trim($p->deskripsi); 

            if ($data instanceof Berkas) {
                if (Str::startsWith($path, 'desa.')) $path = Str::replaceFirst('desa.', 'dataDesa.', $path);
                if (Str::startsWith($path, 'kecamatan.')) $path = Str::replaceFirst('kecamatan.', 'dataKecamatan.', $path);
            }

            $value = data_get($data, $path);

            if ((is_null($value) || $value === '') && $data instanceof Berkas) {
                if (Str::contains(strtolower($path), 'desa') && !empty($data->desa)) {
                    $value = $data->desa;
                } elseif (Str::contains(strtolower($path), 'kecamatan') && !empty($data->kecamatan)) {
                    $value = $data->kecamatan;
                } elseif (!str_contains($path, '.') && isset($data->$path)) {
                    $value = $data->$path;
                }
            }

            if ($value instanceof \DateTime || $value instanceof Carbon) {
                $value = Carbon::parse($value)->format('d-m-Y H:i');
            }

            if (is_array($value) || is_object($value)) $value = '-'; 
            if (is_null($value)) $value = ''; 

            $message = str_replace($search, (string)$value, $message);
        }

        return $message;
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
        try {
            WaLog::create([
                'target_phone' => $number,
                'pesan' => $message,
                'status' => $status,
                'keterangan' => substr((string)$keterangan, 0, 255),
                'berkas_id' => $berkasId,
                'user_id' => $userId ?? auth()->id(),
                'template_id' => $templateId
            ]);
        } catch (Exception $e) {
            Log::error("Gagal simpan WA Log: " . $e->getMessage());
        }
    }
}