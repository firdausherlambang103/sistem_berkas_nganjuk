<?php

namespace App\Http\Controllers;

use App\Models\Berkas;
use App\Models\WaLog;
use App\Models\WaTemplate;
use App\Services\WaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WhatsappWebController extends Controller
{
    public function send(Request $request)
    {
        $request->validate([
            'berkas_id' => 'required|exists:berkas,id',
            'template_id' => 'required|exists:wa_templates,id',
            'nomer_tujuan' => 'required',
        ]);

        $berkas = Berkas::findOrFail($request->berkas_id);
        $template = WaTemplate::findOrFail($request->template_id);

        // 1. Ganti Placeholder
        $pesan = $template->pesan;
        $pesan = str_replace('{nama_pemohon}', $berkas->nama_pemohon, $pesan);
        $pesan = str_replace('{nomer_berkas}', $berkas->nomer_berkas, $pesan);
        $pesan = str_replace('{status}', $berkas->status, $pesan);

        // 2. Kirim via Service
        $result = WaService::send($request->nomer_tujuan, $pesan);

        // 3. Simpan Log
        WaLog::create([
            'berkas_id' => $berkas->id,
            'user_id'   => Auth::id(),
            'target_phone' => $request->nomer_tujuan,
            'pesan'     => $pesan,
            'status'    => $result['status'] ? 'Sukses' : 'Gagal: ' . $result['detail'],
        ]);

        if ($result['status']) {
            return response()->json(['success' => true, 'message' => 'Terkirim!']);
        } else {
            return response()->json(['success' => false, 'message' => $result['detail']]);
        }
    }
}