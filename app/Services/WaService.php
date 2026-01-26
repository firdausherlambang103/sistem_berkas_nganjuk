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

    /**
     * Cek status koneksi ke WA Gateway.
     * Digunakan oleh halaman Admin > Scan WhatsApp.
     */
    public function getStatus()
    {
        try {
            // Mengirim request GET ke endpoint /status gateway
            $response = Http::timeout(3)->get("{$this->baseUrl}/status");
            
            if ($response->successful()) {
                $data = $response->json();
                
                // Normalisasi status text menjadi huruf besar
                $statusText = strtoupper($data['status'] ?? 'UNKNOWN');
                
                // Daftar status yang dianggap "Terhubung"
                // Sesuaikan dengan respon library WA Gateway yang Anda pakai (misal: whatsapp-web.js / Baileys)
                $connected = in_array($statusText, ['CONNECTED', 'READY', 'AUTHENTICATED', 'SUKSES', 'ONLINE']);
                
                return [
                    'connected' => $connected,
                    'status_text' => $statusText
                ];
            }
        } catch (Exception $e) {
            // Log::error("WA Status Check Gagal: " . $e->getMessage());
        }

        // Jika request gagal atau timeout, dianggap tidak terhubung
        return ['connected' => false, 'status_text' => 'OFFLINE'];
    }

    /**
     * Mengambil data QR Code dari Gateway.
     */
    public function getQrCode()
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/qr");
            
            if ($response->successful()) {
                $data = $response->json();
                return [
                    // Gateway biasanya mengembalikan QR dalam format base64 string
                    'qr_code' => $data['qr'] ?? $data['qr_code'] ?? null,
                    'message' => $data['message'] ?? 'Silakan scan QR Code'
                ];
            }
        } catch (Exception $e) {
            return ['message' => 'Gagal menghubungi server WA.'];
        }
        
        return ['message' => 'QR Code belum tersedia.'];
    }

    /**
     * Logout sesi WhatsApp.
     */
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
     * FUNGSI PENGIRIMAN PESAN
     * ====================================================================
     */

    /**
     * Kirim pesan menggunakan Template yang tersimpan di database.
     * @param string $templateName Nama template (kolom 'nama' di tabel wa_templates)
     * @param string $targetPhone Nomor tujuan
     * @param mixed $dataBerkas Objek Berkas atau ID Berkas
     * @param int|null $userId ID User pengirim (opsional)
     */
    public function sendByTemplate($templateName, $targetPhone, $dataBerkas = [], $userId = null)
    {
        // 1. Cari Template
        $template = WaTemplate::where('nama', $templateName)->first();
        
        if (!$template) {
            Log::error("WA Error: Template '$templateName' tidak ditemukan.");
            return ['status' => false, 'message' => 'Template tidak ditemukan'];
        }

        // 2. Siapkan Data Berkas
        $berkas = $this->prepareBerkasData($dataBerkas);
        
        // 3. Ambil isi template mentah
        $isiPesanRaw = $template->template;

        // 4. Parse Placeholder (Ganti {variabel} dengan data asli)
        // Jika data berkas ada, lakukan parsing. Jika tidak, kirim mentah.
        $message = $berkas ? $this->parseTemplate($isiPesanRaw, $berkas) : $isiPesanRaw;
        
        // 5. Kirim
        return $this->send($targetPhone, $message, $berkas ? $berkas->id : null, $userId, $template->id);
    }

    /**
     * Fungsi dasar pengiriman pesan (Raw Send).
     */
    public function send($number, $message, $berkasId = null, $userId = null, $templateId = null)
    {
        try {
            // Format nomor HP (hapus 0 depan, ganti 62, dll)
            $number = $this->formatNumber($number);
            
            if (empty($number)) {
                return ['status' => false, 'message' => 'Nomor tujuan kosong/tidak valid'];
            }

            // Kirim POST request ke Gateway
            $response = Http::timeout(15)->post("{$this->baseUrl}/send-message", [
                'number' => $number,
                'message' => $message,
                'api_key' => $this->apiKey // Jika gateway butuh API Key
            ]);
            
            $responseData = $response->json();
            
            // Tentukan status berdasarkan respon gateway
            // Sesuaikan logika ini dengan respon JSON gateway Anda
            $isSuccess = $response->successful() && (isset($responseData['status']) && $responseData['status'] == true);
            $statusLog = $isSuccess ? 'Sukses' : 'Gagal';
            $keterangan = $responseData['message'] ?? ($isSuccess ? 'Pesan terkirim' : 'Gagal kirim');

            // Catat ke Database (wa_logs)
            $this->logMessage($number, $message, $statusLog, $keterangan, $berkasId, $userId, $templateId);
            
            return [
                'status' => $isSuccess,
                'message' => $keterangan
            ];

        } catch (Exception $e) {
            Log::error("WA Exception to {$number}: " . $e->getMessage());
            
            // Catat log error koneksi
            $this->logMessage($number, $message, 'Gagal', "Koneksi Gateway Error: " . $e->getMessage(), $berkasId, $userId, $templateId);
            
            return ['status' => false, 'message' => 'Gagal koneksi ke Server WA'];
        }
    }

    /**
     * ====================================================================
     * HELPER METHODS (Private/Protected)
     * ====================================================================
     */

    /**
     * Memuat relasi berkas agar data placeholder tersedia.
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
     * Mengganti placeholder {nama} dengan data asli dari objek Berkas.
     * Menggunakan Fallback Logic jika relasi null tapi kolom string ada.
     */
    protected function parseTemplate($message, $data)
    {
        // Ambil semua definisi placeholder dari DB
        $placeholders = WaPlaceholder::all();

        if ($placeholders->isEmpty()) {
            return $message;
        }

        foreach ($placeholders as $p) {
            $search = $p->placeholder; // Contoh: {nama_desa}
            $path = trim($p->deskripsi); // Contoh: desa.nama_desa

            // 1. Normalisasi Path untuk akses Relasi
            // Jika user menulis 'desa.nama', ubah jadi 'dataDesa.nama' agar sesuai nama method relasi di Model
            if ($data instanceof Berkas) {
                if (Str::startsWith($path, 'desa.')) $path = Str::replaceFirst('desa.', 'dataDesa.', $path);
                if (Str::startsWith($path, 'kecamatan.')) $path = Str::replaceFirst('kecamatan.', 'dataKecamatan.', $path);
            }

            // 2. Coba ambil data menggunakan dot notation
            $value = data_get($data, $path);

            // 3. [SMART FALLBACK] 
            // Jika data dari relasi kosong (null), cek apakah ada kolom string langsung di tabel berkas.
            // Contoh: Relasi 'dataDesa' null, tapi kolom 'desa' berisi string "Sukorejo".
            if ((is_null($value) || $value === '') && $data instanceof Berkas) {
                
                // Cek fallback untuk Desa
                if (Str::contains(strtolower($path), 'desa') && !empty($data->desa)) {
                    $value = $data->desa;
                }
                // Cek fallback untuk Kecamatan
                elseif (Str::contains(strtolower($path), 'kecamatan') && !empty($data->kecamatan)) {
                    $value = $data->kecamatan;
                }
                // Cek properti langsung (misal: 'nama_pemohon')
                elseif (!str_contains($path, '.') && isset($data->$path)) {
                    $value = $data->$path;
                }
            }

            // 4. Format Tanggal otomatis
            if ($value instanceof \DateTime || $value instanceof Carbon) {
                $value = Carbon::parse($value)->format('d-m-Y H:i');
            }

            // 5. Cleanup nilai kosong/array
            if (is_array($value) || is_object($value)) {
                $value = '-'; 
            }
            if (is_null($value)) {
                $value = ''; 
            }

            // Replace di pesan
            $message = str_replace($search, (string)$value, $message);
        }

        return $message;
    }

    /**
     * Format nomor HP ke standar WA (62xxx@c.us)
     */
    protected function formatNumber($number)
    {
        // Hapus karakter non-angka
        $number = preg_replace('/[^0-9]/', '', $number);
        
        if (empty($number)) return '';

        // Ubah 08xx jadi 628xx
        if (substr($number, 0, 1) == '0') {
            $number = '62' . substr($number, 1);
        }
        // Jika tidak diawali 62 (dan bukan 0), tambahkan 62
        if (substr($number, 0, 2) != '62') {
            $number = '62' . $number;
        }

        // Tambahkan suffix gateway jika belum ada
        if (!str_ends_with($number, '@c.us')) {
            $number .= '@c.us';
        }

        return $number;
    }

    /**
     * Simpan Log ke Database
     */
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