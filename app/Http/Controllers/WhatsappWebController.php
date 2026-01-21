<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WaService;
use Illuminate\Support\Facades\Http;

class WhatsappWebController extends Controller
{
    protected $waService;

    public function __construct(WaService $waService)
    {
        $this->waService = $waService;
    }

    /**
     * Halaman Scan QR Code (Integrasi dengan WA Gateway Anda)
     */
    public function scan()
    {
        // URL endpoint WA Gateway Anda untuk mendapatkan QR/Status
        // Sesuaikan dengan API yang dipakai di aplikasi-berkas
        $waUrl = env('WA_API_URL', 'http://localhost:3000'); 
        
        return view('admin.whatsapp.scan', compact('waUrl'));
    }

    /**
     * Tes Kirim Pesan Manual
     */
    public function sendTest(Request $request)
    {
        $request->validate([
            'number' => 'required',
            'message' => 'required',
        ]);

        $result = $this->waService->send($request->number, $request->message);

        if (isset($result['status']) && $result['status'] == false) {
             return back()->with('error', 'Gagal mengirim pesan: ' . ($result['message'] ?? 'Unknown error'));
        }

        return back()->with('success', 'Pesan berhasil dikirim!');
    }
    
    /**
     * Logout Session WA
     */
    public function logout()
    {
        try {
            $waUrl = env('WA_API_URL', 'http://localhost:3000');
            Http::post("$waUrl/logout", ['api_key' => env('WA_API_KEY')]);
            return back()->with('success', 'WhatsApp berhasil logout.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal logout: ' . $e->getMessage());
        }
    }
}