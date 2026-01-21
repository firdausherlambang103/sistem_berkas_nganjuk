<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WaService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log; // Tambahkan Log

class WhatsappWebController extends Controller
{
    protected $waService;

    public function __construct(WaService $waService)
    {
        $this->waService = $waService;
    }

    public function scan()
    {
        $waUrl = env('WA_API_URL', 'http://192.168.100.15:3000');
        return view('admin.whatsapp.scan', compact('waUrl'));
    }

    // --- FUNGSI UTAMA PENGIRIMAN (Digunakan oleh Ruang Kerja) ---
    public function send(Request $request)
    {
        try {
            // 1. Validasi Input
            $request->validate([
                'number' => 'required',  // Pastikan JS mengirim key 'number'
                'message' => 'required', // Pastikan JS mengirim key 'message'
            ]);

            // 2. Ambil Data
            $number = $request->number;
            $message = $request->message;
            $berkasId = $request->berkas_id ?? null; // Opsional
            
            // 3. Panggil Service
            $result = $this->waService->send($number, $message, $berkasId, auth()->id());

            // 4. Cek Hasil dari Service
            if (isset($result['status']) && $result['status'] == true) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pesan berhasil dikirim!'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Gagal terhubung ke server WA.'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('WA Controller Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error Server: ' . $e->getMessage()
            ], 500);
        }
    }

    // --- FUNGSI TEST MANUAL (Digunakan oleh Halaman Scan) ---
    public function sendTest(Request $request)
    {
        $request->validate([
            'number' => 'required',
            'message' => 'required',
        ]);

        $result = $this->waService->send($request->number, $request->message);

        if (isset($result['status']) && $result['status'] == false) {
             return back()->with('error', 'Gagal: ' . ($result['message'] ?? 'Unknown error'));
        }

        return back()->with('success', 'Pesan berhasil dikirim!');
    }
    
    public function logout()
    {
        try {
            $waUrl = env('WA_API_URL', 'http://192.168.100.15:3000');
            Http::post("$waUrl/logout");
            return back()->with('success', 'WhatsApp berhasil logout.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal logout: ' . $e->getMessage());
        }
    }
}