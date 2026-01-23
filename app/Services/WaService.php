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

        // 1. Siapkan Data Berkas & Load Relasi
        $berkas = $this->prepareBerkasData($dataBerkas);

        if (!$berkas) {
            // Kirim pesan mentah jika data berkas gagal dimuat
            return $this->send($targetPhone, $template->isi_pesan, null, $userId, $template->id);
        }

        // 2. Parse Placeholder (Ganti {nama} dengan data asli)
        $message = $this->parseTemplate($template->isi_pesan, $berkas);
        
        return $this->send($targetPhone, $message, $berkas->id, $userId, $template->id);
    }

    public function send($number, $message, $berkasId = null, $userId = null, $templateId = null)
    {
        try {
            $number = $this->formatNumber($number);
            
            // Log payload untuk debugging
            // Log::info("Sending WA to $number", ['message' => $message]);

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
     * Memuat relasi yang diperlukan agar placeholder (misal: {desa.nama_desa}) bisa terbaca.
     */
    protected function prepareBerkasData($data)
    {
        $id = null;
        if ($data instanceof Berkas) $id = $data->id;
        elseif (is_array($data)) $id = $data['id'] ?? ($data['berkas_id'] ?? null);
        elseif (is_numeric($data)) $id = $data;

        if (!$id) return null;

        // [PENTING] Load semua relasi yang mungkin dipakai di placeholder
        $relations = [
            'jenisPermohonan', 
            'dataDesa',       // Relasi ke Model Desa (pastikan nama fungsi di Berkas.php adalah dataDesa)
            'dataKecamatan',  // Relasi ke Model Kecamatan (pastikan nama fungsi di Berkas.php adalah dataKecamatan)
            'petugasUkur', 
            'penerimaKuasa', 
            'posisiSekarang', 
            'pengirim',
            'user'            // Pemohon/User pembuat
        ];

        // Gunakan try-catch untuk menghindari error jika relasi tidak ditemukan di model
        try {
            return Berkas::with($relations)->find($id);
        } catch (\Exception $e) {
            Log::warning("WaService: Gagal load relasi. Menggunakan data berkas dasar. Error: " . $e->getMessage());
            return Berkas::find($id);
        }
    }

    /**
     * Fungsi parsing yang menggunakan Tabel WaPlaceholder sebagai kamus data.
     */
    protected function parseTemplate($message, $data)
    {
        // 1. Ambil semua definisi placeholder dari Database
        $placeholders = WaPlaceholder::all();

        if ($placeholders->isEmpty()) {
            return $message;
        }

        foreach ($placeholders as $p) {
            $search = $p->placeholder; // Contoh: {nama_desa}
            $path = trim($p->deskripsi); // Contoh: desa.nama_desa atau dataDesa.nama_desa

            // [FIX CRITICAL] Normalisasi Path Relasi
            // Jika di database tertulis 'desa.nama_desa' tapi di model relasinya 'dataDesa'
            if ($data instanceof Berkas) {
                // Ubah 'desa.' menjadi 'dataDesa.' agar menunjuk ke Relasi, bukan kolom string 'desa'
                if (Str::startsWith($path, 'desa.')) {
                    $path = Str::replaceFirst('desa.', 'dataDesa.', $path);
                }
                // Ubah 'kecamatan.' menjadi 'dataKecamatan.'
                if (Str::startsWith($path, 'kecamatan.')) {
                    $path = Str::replaceFirst('kecamatan.', 'dataKecamatan.', $path);
                }
            }

            // 2. Ambil value menggunakan data_get (support dot notation)
            $value = data_get($data, $path);

            // 3. Fallback: Jika data_get gagal (null), coba ambil langsung dari kolom biasa
            // Misal path 'nama_desa' (tanpa titik), coba cek apakah ada kolom 'nama_desa' atau 'desa'
            if (is_null($value) && !str_contains($path, '.')) {
                // Cek kolom 'desa' biasa jika 'nama_desa' null
                if ($path === 'nama_desa' && isset($data->desa)) {
                    $value = $data->desa;
                }
                if ($path === 'nama_kecamatan' && isset($data->kecamatan)) {
                    $value = $data->kecamatan;
                }
            }

            // 4. Format Tanggal
            if ($value instanceof \DateTime || $value instanceof Carbon) {
                $value = Carbon::parse($value)->format('d-m-Y H:i');
            }

            // 5. Cleanup Value
            if (is_array($value) || is_object($value)) {
                $value = '-'; 
            }
            if (is_null($value) || $value === '') {
                $value = '-'; 
            }

            // 6. Replace di pesan
            $message = str_replace($search, (string)$value, $message);
        }

        return $message;
    }

    protected function formatNumber($number)
    {
        $number = preg_replace('/[^0-9]/', '', $number);
        
        if (empty($number)) return '';

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