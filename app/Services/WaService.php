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

    public function sendByTemplate($templateName, $targetPhone, $dataBerkas = [], $userId = null)
    {
        $template = WaTemplate::where('nama_template', $templateName)->first();
        if (!$template) {
            Log::error("WA Error: Template '$templateName' tidak ditemukan.");
            return ['status' => false, 'message' => 'Template tidak ditemukan'];
        }

        // Load Relasi
        $berkas = $this->prepareBerkasData($dataBerkas);

        if (!$berkas) {
            return $this->send($targetPhone, $template->isi_pesan, null, $userId, $template->id);
        }

        // Parse Placeholder
        $message = $this->parseTemplate($template->isi_pesan, $berkas);
        
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

    protected function prepareBerkasData($data)
    {
        $id = null;
        if ($data instanceof Berkas) $id = $data->id;
        elseif (is_array($data)) $id = $data['id'] ?? ($data['berkas_id'] ?? null);
        elseif (is_numeric($data)) $id = $data;

        if (!$id) return null;

        // [PENTING] Load relasi baru (dataDesa, dataKecamatan)
        $potentialRelations = [
            'jenisPermohonan', 'jenis_permohonan',
            'dataDesa', // <-- Ambil dari relasi baru
            'dataKecamatan', // <-- Ambil dari relasi baru
            'user', 'petugas', 
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

        return Berkas::with($validRelations)->find($id);
    }

    protected function parseTemplate($message, $data)
    {
        $placeholders = WaPlaceholder::all();
        
        foreach ($placeholders as $p) {
            $key = $p->placeholder; // contoh: {nama_desa}
            $fieldRaw = trim($p->deskripsi); // contoh: desa.nama_desa
            
            $value = '-'; 

            // Jika placeholder merujuk ke relasi (ada titiknya)
            if (str_contains($fieldRaw, '.')) {
                $parts = explode('.', $fieldRaw, 2);
                $relName = $parts[0]; // 'desa'
                $colName = $parts[1]; // 'nama_desa'

                // [SOLUSI PENYELAMAT] 
                // Jika database meminta 'desa', kita PAKSA alihkan ke 'dataDesa'
                // Ini mencegah sistem mengambil kolom angka 'desa'
                if ($relName === 'desa') $relName = 'dataDesa';
                if ($relName === 'kecamatan') $relName = 'dataKecamatan';

                // Ambil data dari relasi
                if ($data->$relName) {
                    $value = $data->$relName->$colName ?? null;
                }
            } 
            // Jika placeholder kolom biasa (tidak ada titik)
            else {
                $value = $data->$fieldRaw ?? null;
            }

            // Format Tanggal
            if ($value instanceof Carbon || $value instanceof \DateTime) {
                $value = Carbon::parse($value)->format('d-m-Y H:i');
            }

            // Cleanup
            if (is_null($value) || is_array($value) || is_object($value) || $value === '') {
                $value = '-'; 
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