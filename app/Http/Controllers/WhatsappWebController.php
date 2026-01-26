<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WaService;
use Illuminate\Support\Facades\Log;

class WhatsappWebController extends Controller
{
    protected $waService;

    public function __construct(WaService $waService)
    {
        $this->waService = $waService;
    }

    public function scan()
    {
        return view('admin.whatsapp.scan');
    }

    // [METHOD BARU] Cek Status Koneksi
    public function checkStatus()
    {
        // Pastikan WaService memiliki method getStatus()
        $status = $this->waService->getStatus();
        return response()->json($status);
    }

    // [METHOD BARU] Ambil QR Code
    public function getQr()
    {
        // Pastikan WaService memiliki method getQrCode()
        $qr = $this->waService->getQrCode();
        return response()->json($qr);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'number' => 'required',
            'message' => 'required',
        ]);

        // Kirim pesan
        $result = $this->waService->send(
            $request->number, 
            $request->message, 
            $request->berkas_id ?? null, 
            auth()->id(), 
            $request->template_id ?? null
        );

        if (isset($result['status']) && $result['status'] == true) {
            return response()->json(['success' => true, 'message' => 'Pesan berhasil dikirim!']);
        } else {
            return response()->json(['success' => false, 'message' => $result['message'] ?? 'Gagal kirim.']);
        }
    }

    public function logout()
    {
        $this->waService->logout();
        return back()->with('success', 'Logout command sent.');
    }

    public function sendTest(Request $request)
    {
        $request->validate(['number' => 'required']);
        $this->waService->send($request->number, "Tes koneksi WhatsApp dari Sistem Berkas.");
        return back()->with('success', 'Pesan tes dikirim.');
    }
}