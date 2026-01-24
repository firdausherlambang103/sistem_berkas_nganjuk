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
        $this->baseUrl = env('WA_API_URL', 'http://192.168.100.15:3000');
        $this->apiKey = env('WA_API_KEY', '');
    }

    /**
     * Kirim pesan berdasarkan nama template.
     */
    public function sendByTemplate($templateName, $targetPhone, $dataBerkas = [], $userId = null)
    {
        // [FIX] Gunakan kolom 'nama' bukan 'nama_template'
        $template = WaTemplate::where('nama', $templateName)->first();
        
        if (!$template) {
            Log::error("WA Error: Template '$templateName' tidak ditemukan di database.");
            return ['status' => false, 'message' => 'Template tidak ditemukan'];
        }

        // 1. Siapkan Data Berkas & Load Relasi
        $berkas = $this->prepareBerkasData($dataBerkas);

        // [FIX] Gunakan kolom 'template' bukan 'isi_pesan'
        $isiPesan = $template->template;

        if (!$berkas) {
            return $this->send($targetPhone, $isiPesan, null, $userId, $template->id);
        }

        // 2. Parse Placeholder (Ganti {nama} dengan data asli)
        $message = $this->parseTemplate($isiPesan, $berkas);
        
        return $this->send($targetPhone, $message, $berkas->id, $userId, $template->id);
    }

    /**
     * Kirim pesan langsung (raw message).
     */
    public function send($number, $message, $berkasId = null, $userId = null, $templateId = null)
    {
        try {
            $number = $this->formatNumber($number);
            
            if (empty($number)) {
                return ['status' => false, 'message' => 'Nomor tujuan kosong'];
            }

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

    /**
     * Memuat relasi yang diperlukan agar placeholder bisa terbaca.
     */
    protected function prepareBerkasData($data)
    {
        $id = null;
        if ($data instanceof Berkas) $id = $data->id;
        elseif (is_array($data)) $id = $data['id'] ?? ($data['berkas_id'] ?? null);
        elseif (is_numeric($data)) $id = $data;

        if (!$id) return null;

        $relations = [
            'jenisPermohonan', 
            'dataDesa',       
            'dataKecamatan',  
            'petugasUkur', 
            'penerimaKuasa', 
            'posisiSekarang', 
            'pengirim',
            'user'            
        ];

        try {
            return Berkas::with($relations)->find($id);
        } catch (\Exception $e) {
            return Berkas::find($id);
        }
    }

    /**
     * Fungsi parsing Placeholder.
     */
    protected function parseTemplate($message, $data)
    {
        $placeholders = WaPlaceholder::all();

        if ($placeholders->isEmpty()) {
            return $message;
        }

        foreach ($placeholders as $p) {
            $search = $p->placeholder; 
            $path = trim($p->deskripsi); 

            // [FIX LOGIKA] Mapping Relasi vs Kolom String
            // Jika path meminta relasi (misal: desa.nama_desa) tapi data berkas menyimpan string di kolom 'desa'
            // Maka kita paksa ambil nilai kolom string tersebut.
            
            $value = null;

            // 1. Coba ambil menggunakan data_get standard (support dot notation)
            // Ubah alias dulu jika diperlukan
            $lookupPath = $path;
            if (Str::startsWith($lookupPath, 'desa.')) $lookupPath = Str::replaceFirst('desa.', 'dataDesa.', $lookupPath);
            if (Str::startsWith($lookupPath, 'kecamatan.')) $lookupPath = Str::replaceFirst('kecamatan.', 'dataKecamatan.', $lookupPath);
            
            $value = data_get($data, $lookupPath);

            // 2. [FALLBACK ROBUST] Jika hasil null, dan path berkaitan dengan 'desa' atau 'kecamatan'
            // Kita cek apakah ada data string langsung di tabel berkas
            if (empty($value) && $data instanceof Berkas) {
                if (Str::contains(strtolower($path), 'desa') && !empty($data->desa)) {
                    $value = $data->desa;
                }
                elseif (Str::contains(strtolower($path), 'kecamatan') && !empty($data->kecamatan)) {
                    $value = $data->kecamatan;
                }
                // Cek direct property (misal: 'nama_pemohon')
                elseif (isset($data->$path)) {
                    $value = $data->$path;
                }
            }

            // 3. Format Tanggal
            if ($value instanceof \DateTime || $value instanceof Carbon) {
                $value = Carbon::parse($value)->format('d-m-Y H:i');
            }

            // 4. Cleanup
            if (is_array($value) || is_object($value)) {
                $value = '-'; 
            }
            if (is_null($value)) {
                $value = ''; // Jangan tampilkan '-' jika kosong, lebih baik string kosong atau '-' sesuai selera
            }

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