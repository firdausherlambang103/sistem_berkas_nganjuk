<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WaService;
use App\Models\WaTemplate; // Tambahan Model
use App\Models\Berkas;     // Tambahan Model
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

    // Cek Status Koneksi
    public function checkStatus()
    {
        $status = $this->waService->getStatus();
        return response()->json($status);
    }

    // Ambil QR Code
    public function getQr()
    {
        $qr = $this->waService->getQrCode();
        return response()->json($qr);
    }

    // [DIPERBARUI] Method Kirim Pesan WA + PDF
    public function sendMessage(Request $request)
    {
        $request->validate([
            'number' => 'required',
            'message' => 'required',
        ]);

        $mediaUrls = [];

        // 1. TANGKAP ULANG PDF SEBELUM DIKIRIM
        // Karena di front-end hanya tampil teks, kita pancing ulang file PDF-nya di sini
        if ($request->filled('template_id') && $request->filled('berkas_id')) {
            $template = WaTemplate::find($request->template_id);
            $berkas = Berkas::with([
                'dataDesa', 'dataKecamatan', 'jenisPermohonan', 'posisiSekarang', 'penerimaKuasa'
            ])->find($request->berkas_id);
            
            if ($template && $berkas) {
                // Ekstrak URL/Path PDF dari template asli
                $parsedData = $this->waService->parseMediaTemplate($template->template, $berkas);
                $mediaUrls = $parsedData['media_urls']; 
            }
        }

        // 2. BERSIHKAN TEKS PRATINJAU
        // Buang tulisan indikator miring agar tidak ikut terkirim ke HP warga
        $pesanBersih = str_replace("\n\n*(Sistem akan mengirimkan dokumen PDF/Lampiran secara otomatis)*", "", $request->message);

        // 3. EKSEKUSI KIRIM WA
        $result = $this->waService->send(
            $request->number, 
            $pesanBersih, 
            $request->berkas_id ?? null, 
            auth()->id(), 
            $request->template_id ?? null,
            $mediaUrls // <--- Data PDF dimasukkan ke sini
        );

        if (isset($result['status']) && $result['status'] == true) {
            return response()->json(['success' => true, 'message' => 'Pesan dan berkas berhasil dikirim!']);
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