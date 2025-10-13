<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Desa;
use Illuminate\Http\Request;

class WilayahController extends Controller
{
    /**
     * Mengambil daftar desa berdasarkan ID kecamatan.
     */
    public function getDesa(Request $request)
    {
        // Validasi untuk memastikan kecamatan_id dikirim
        $request->validate([
            'kecamatan_id' => 'required|exists:kecamatans,id'
        ]);

        $kecamatan_id = $request->kecamatan_id;

        $desas = Desa::where('kecamatan_id', $kecamatan_id)
                     ->orderBy('nama_desa', 'asc')
                     ->get(['id', 'nama_desa']); // Ambil hanya kolom yang perlu

        // Kembalikan data dalam format JSON
        return response()->json($desas);
    }
}