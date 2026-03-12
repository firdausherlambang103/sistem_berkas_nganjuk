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
use Throwable;

class WaService
{
    protected $baseUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->baseUrl = env('WA_API_URL', 'http://127.0.0.1:3000');
        $this->apiKey = env('WA_API_KEY', '');
    }

    public function getStatus()
    {
        try {
            $response = Http::timeout(3)->get("{$this->baseUrl}/status");
            if ($response->successful()) {
                $data = $response->json();
                $statusText = strtoupper($data['status'] ?? 'UNKNOWN');
                $connected = in_array($statusText, ['CONNECTED', 'READY', 'AUTHENTICATED', 'SUKSES', 'ONLINE']);
                return ['connected' => $connected, 'status_text' => $statusText];
            }
        } catch (Throwable $e) {}
        return ['connected' => false, 'status_text' => 'OFFLINE'];
    }

    public function getQrCode()
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/qr");
            if ($response->successful()) {
                $data = $response->json();
                return ['qr_code' => $data['qr'] ?? $data['qr_code'] ?? null, 'message' => $data['message'] ?? 'Silakan scan QR Code'];
            }
        } catch (Throwable $e) {
            return ['message' => 'Gagal menghubungi server WA.'];
        }
        return ['message' => 'QR Code belum tersedia.'];
    }

    public function logout()
    {
        try {
            Http::timeout(5)->post("{$this->baseUrl}/logout");
        } catch (Throwable $e) {
            Log::error("WA Logout Error: " . $e->getMessage());
        }
    }

    public function sendByTemplate($templateName, $targetPhone, $dataBerkas = [], $userId = null)
    {
        $template = WaTemplate::where('nama', $templateName)->first();
        
        if (!$template) {
            Log::error("WA Error: Template '$templateName' tidak ditemukan.");
            return ['status' => false, 'message' => 'Template tidak ditemukan'];
        }

        $berkas = $this->prepareBerkasData($dataBerkas);
        $isiPesanRaw = $template->template ?? '';

        $parsedData = $berkas ? $this->parseMediaTemplate($isiPesanRaw, $berkas) : ['message' => $isiPesanRaw, 'media_urls' => []];
        
        return $this->send($targetPhone, $parsedData['message'], $berkas ? $berkas->id : null, $userId, $template->id, $parsedData['media_urls']);
    }

    public function send($number, $message, $berkasId = null, $userId = null, $templateId = null, $mediaUrls = [])
    {
        try {
            $number = $this->formatNumber($number);
            if (empty($number)) {
                return ['status' => false, 'message' => 'Nomor tujuan kosong/tidak valid'];
            }

            $payload = [
                'number' => $number,
                'message' => $message,
                'api_key' => $this->apiKey
            ];

            // [DIPERBARUI] Kirim Path LOKAL DAN Path URL secara bersamaan
            if (!empty($mediaUrls)) {
                $payload['media_path'] = $mediaUrls[0]['path']; 
                $payload['media_url']  = $mediaUrls[0]['url']; 
            }

            // Waktu timeout dinaikkan ke 45 detik jaga-jaga download file agak lama
            $response = Http::timeout(45)->post("{$this->baseUrl}/send-message", $payload);
            $responseData = $response->json();
            
            $isSuccess = $response->successful() && (isset($responseData['status']) && $responseData['status'] == true);
            $statusLog = $isSuccess ? 'Sukses' : 'Gagal';
            $keterangan = $responseData['message'] ?? ($isSuccess ? 'Pesan terkirim' : 'Gagal kirim');

            $this->logMessage($number, $message, $statusLog, $keterangan, $berkasId, $userId, $templateId, $mediaUrls);
            return ['status' => $isSuccess, 'message' => $keterangan];

        } catch (Throwable $e) {
            Log::error("WA Exception to {$number}: " . $e->getMessage());
            $this->logMessage($number, $message, 'Gagal', "Koneksi Gateway Error: " . substr($e->getMessage(), 0, 100), $berkasId, $userId, $templateId, $mediaUrls);
            return ['status' => false, 'message' => 'Gagal koneksi ke Server WA'];
        }
    }

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
        } catch (Throwable $e) {
            return Berkas::find($id);
        }
    }

    public function parseMediaTemplate($message, $data)
    {
        $message = $message ?? ''; 
        $placeholders = WaPlaceholder::all();
        $mediaData = [];

        if ($placeholders->isEmpty()) {
            return ['message' => $message, 'media_urls' => []];
        }

        foreach ($placeholders as $p) {
            $search = $p->placeholder; 
            $path = trim($p->deskripsi ?? ''); 

            if (empty($search) || empty($path)) continue;

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
                } elseif (!Str::contains($path, '.') && isset($data->$path)) { 
                    $value = $data->$path;
                }
            }

            // [DIPERBARUI] Tangkap file dan pecah menjadi Absolute Path Harddisk & Absolute URL
            if (is_string($value) && preg_match('/\.(pdf|jpg|jpeg|png)$/i', $value)) {
                // Hilangkan slash di awal agar tidak campur-aduk
                $cleanPath = ltrim($value, '/\\');
                
                // Normalisasi Path Windows (Biar slash-nya benar)
                $absolutePath = storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $cleanPath));
                
                // URL Publik otomatis bawa IP yg sedang diakses (cth: 192.168.0.81:8000)
                $publicUrl = asset('storage/' . $cleanPath);

                $mediaData[] = [
                    'path' => $absolutePath,
                    'url'  => $publicUrl
                ];
                
                $message = str_replace($search, '', $message);
                continue;
            }

            if ($value instanceof \DateTime || $value instanceof Carbon) {
                $value = Carbon::parse($value)->format('d-m-Y H:i');
            }

            if (is_array($value) || is_object($value)) {
                $value = '-'; 
            }
            if (is_null($value)) {
                $value = ''; 
            }

            $message = str_replace($search, (string)$value, $message);
        }

        return [
            'message' => trim($message),
            'media_urls' => $mediaData
        ];
    }

    protected function formatNumber($number)
    {
        $number = preg_replace('/[^0-9]/', '', $number ?? '');
        if (empty($number)) return '';
        if (substr($number, 0, 1) == '0') $number = '62' . substr($number, 1);
        if (substr($number, 0, 2) != '62') $number = '62' . $number;
        if (!Str::endsWith($number, '@c.us')) $number .= '@c.us'; 
        return $number;
    }

    protected function logMessage($number, $message, $status, $keterangan, $berkasId, $userId, $templateId, $mediaUrls = [])
    {
        try {
            $logPesan = substr($message ?? '', 0, 400);
            if (!empty($mediaUrls)) {
                // Catat nama filenya
                $logPesan .= "\n\n[Lampiran: " . substr(basename($mediaUrls[0]['path']), 0, 50) . "]";
            }

            WaLog::create([
                'target_phone' => $number,
                'pesan' => $logPesan,
                'status' => $status,
                'keterangan' => substr((string)$keterangan, 0, 255),
                'berkas_id' => $berkasId,
                'user_id' => $userId ?? auth()->id(),
                'template_id' => $templateId
            ]);
        } catch (Throwable $e) {
            Log::error("Gagal simpan WA Log: " . $e->getMessage());
        }
    }
}