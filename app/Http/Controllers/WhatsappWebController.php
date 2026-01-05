<?php

namespace App\Http\Controllers; // Namespace umum

use App\Http\Controllers\Controller;
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
        // ... validasi & replace message ...

        // 1. Kirim via Service
        $result = WaService::send($request->nomer_tujuan, $pesan);

        // 2. Simpan Log ke Database
        WaLog::create([
            'berkas_id'    => $berkas->id,
            'user_id'      => Auth::id(),
            'target_phone' => $request->nomer_tujuan,
            'pesan'        => $pesan,
            'status'       => $result['status'] ? 'Sukses' : 'Gagal',
            'keterangan'   => $result['detail']
        ]);

        return response()->json([
            'success' => $result['status'], 
            'message' => $result['detail']
        ]);
    }
}