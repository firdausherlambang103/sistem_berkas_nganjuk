<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WaService;
use App\Models\Berkas;
use App\Models\WaTemplate;
use Illuminate\Support\Facades\Log;

class WhatsappWebController extends Controller
{
    protected $waService;

    public function __construct(WaService $waService)
    {
        $this->waService = $waService;
    }

    /**
     * Halaman Scan QR (Jika diperlukan)
     */
    public function scan()
    {
        return view('admin.whatsapp.scan');
    }

    /**
     * Method Utama Pengiriman Pesan (Digunakan oleh Modal Ruang Kerja)
     */
    public function sendMessage(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'number' => 'required',
            'message' => 'required',
            'berkas_id' => 'nullable',
            'template_id' => 'nullable',
        ]);

        try {
            $number = $request->number;
            $message = $request->message;
            $berkasId = $request->berkas_id;
            $templateId = $request->template_id;
            $userId = auth()->id();

            // 2. Kirim Pesan via Service
            // Kita kirim pesan 'raw' karena pesan sudah diparsing/diedit di Frontend (Modal)
            $result = $this->waService->send($number, $message, $berkasId, $userId, $templateId);

            if (isset($result['status']) && $result['status'] == true) {
                return response()->json(['success' => true, 'message' => 'Pesan berhasil dikirim!']);
            } else {
                return response()->json([
                    'success' => false, 
                    'message' => $result['message'] ?? 'Gagal terhubung ke Gateway WA.'
                ]);
            }

        } catch (\Exception $e) {
            Log::error("WA Controller Error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan internal.']);
        }
    }

    // [BARU] Endpoint Cek Status
    public function checkStatus()
    {
        // Panggil Service untuk cek status ke Gateway
        $status = $this->waService->getStatus();
        return response()->json($status);
    }

    // [BARU] Endpoint Ambil QR
    public function getQr()
    {
        // Panggil Service untuk ambil QR Code (Base64 atau URL)
        $qr = $this->waService->getQrCode();
        return response()->json($qr);
    }

    /**
     * Logout / Disconnect WA
     */
    public function logout()
    {
        // Implementasi logout jika API mendukung
        return back()->with('success', 'Sesi WhatsApp dibersihkan.');
    }

    /**
     * Test Kirim (Opsional)
     */
    public function sendTest(Request $request)
    {
        $request->validate(['number' => 'required']);
        $this->waService->send($request->number, "Tes koneksi WhatsApp berhasil.");
        return back()->with('success', 'Pesan tes dikirim.');
    }
}