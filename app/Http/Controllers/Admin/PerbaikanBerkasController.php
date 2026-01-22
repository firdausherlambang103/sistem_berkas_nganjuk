<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Berkas;
use App\Models\User;
use App\Models\RiwayatBerkas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PerbaikanBerkasController extends Controller
{
    public function index(Request $request)
    {
        $berkas = null;
        // Ambil user aktif selain user yang sedang login
        $users = User::where('id', '!=', Auth::id())->orderBy('name')->get(); 

        // Logika Pencarian yang diperbarui
        if ($request->has('keyword') && $request->keyword != '') {
            $query = Berkas::with(['posisiSekarang', 'jenisPermohonan']);

            // Filter berdasarkan Keyword (Nomor atau Nama)
            $query->where(function($q) use ($request) {
                $q->where('nomer_berkas', 'like', '%' . $request->keyword . '%')
                  ->orWhere('nama_pemohon', 'like', '%' . $request->keyword . '%');
            });

            // [TAMBAHAN] Filter berdasarkan Tahun jika diisi
            if ($request->has('tahun') && $request->tahun != '') {
                $query->where('tahun', $request->tahun);
            }

            // Ambil data pertama yang cocok
            $berkas = $query->first();
        }

        return view('admin.berkas.perbaikan', compact('berkas', 'users'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'target_user_id' => 'required|exists:users,id',
            'catatan' => 'required|string|max:255',
        ]);

        $berkas = Berkas::findOrFail($id);
        
        $oldUserName = $berkas->posisiSekarang ? $berkas->posisiSekarang->name : 'Sistem';
        $newUser = User::find($request->target_user_id);

        try {
            DB::transaction(function () use ($berkas, $request, $newUser, $oldUserName) {
                
                // 1. Catat Riwayat
                $riwayat = new RiwayatBerkas();
                $riwayat->berkas_id = $berkas->id;
                $riwayat->dari_user_id = Auth::id(); 
                $riwayat->ke_user_id = $newUser->id; 
                $riwayat->waktu_kirim = Carbon::now();
                $riwayat->catatan_pengiriman = "PERBAIKAN ADMIN: Berkas dipindah paksa dari [{$oldUserName}] ke [{$newUser->name}]. Alasan: {$request->catatan}";
                $riwayat->save();

                // 2. Update Posisi & Reset Status Pengiriman
                $berkas->update([
                    'posisi_sekarang_user_id' => $newUser->id,
                    'status' => 'Diproses',
                    'status_pengiriman' => 'Diterima',
                    'pengirim_id' => null, 
                    'penerima_id' => null, 
                ]);

            });

            return redirect()->route('admin.perbaikan.index', [
                'keyword' => $berkas->nomer_berkas,
                'tahun' => $berkas->tahun // Redirect membawa parameter tahun agar hasil tetap muncul
            ])->with('success', "SUKSES: Berkas dipindahkan ke {$newUser->name}.");

        } catch (\Exception $e) {
            return back()->with('error', 'GAGAL: ' . $e->getMessage());
        }
    }
}