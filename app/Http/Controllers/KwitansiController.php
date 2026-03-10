<?php

namespace App\Http\Controllers;

use App\Models\Berkas;
use Illuminate\Http\Request;
use Carbon\Carbon;

class KwitansiController extends Controller
{
    // Menampilkan halaman Kwitansi dengan fitur Pencarian Detail
    public function index(Request $request)
    {
        $search = $request->input('search');

        // Ambil data yang sudah dibayar saja beserta relasinya
        $query = Berkas::whereNotNull('tgl_bayar')->with(['jenisPermohonan']);

        // Jika ada input pencarian
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nomer_berkas', 'like', "%{$search}%")
                  ->orWhere('nama_pemohon', 'like', "%{$search}%")
                  ->orWhere('penerima_kwitansi', 'like', "%{$search}%")
                  ->orWhere('desa', 'like', "%{$search}%")
                  ->orWhere('kecamatan', 'like', "%{$search}%")
                  ->orWhere('nomer_hak', 'like', "%{$search}%")
                  ->orWhereHas('jenisPermohonan', function($subQuery) use ($search) {
                      $subQuery->where('nama_permohonan', 'like', "%{$search}%");
                  });
            });
        }

        $berkas = $query->orderBy('tgl_bayar', 'desc')->get();
        
        return view('kwitansi.index', compact('berkas', 'search'));
    }

    // Aksi ketika tombol "Sudah Dibayar" di klik di Ruang Kerja
    public function tandaiDibayar(Request $request, $id)
    {
        $request->validate([
            'tgl_bayar' => 'required|date'
        ]);

        $berkas = Berkas::findOrFail($id);
        $berkas->tgl_bayar = $request->tgl_bayar;

        // LOGIKA ARGO DIMULAI DARI SINI (Hanya jika argo belum jalan)
        if (is_null($berkas->waktu_mulai_proses)) {
            $tglMulai = Carbon::parse($request->tgl_bayar)->startOfDay();
            $berkas->waktu_mulai_proses = $tglMulai;

            // Hitung batas waktu berdasarkan SLA jenis permohonan
            if ($berkas->jenisPermohonan && $berkas->jenisPermohonan->waktu_penyelesaian) {
                $hariSla = $berkas->jenisPermohonan->waktu_penyelesaian;
                $berkas->batas_waktu = $tglMulai->copy()->addDays($hariSla)->endOfDay();
            }
        }

        $berkas->save();

        return redirect()->back()->with('success', 'Berkas berhasil ditandai sudah dibayar dan Argo (SLA) telah dimulai!');
    }

    // Aksi ketika tombol "Diserahkan" di klik di menu Kwitansi
    public function serahkanKwitansi(Request $request, $id)
    {
        $request->validate([
            'penerima_kwitansi' => 'required|string|max:255',
            'tgl_penyerahan_kwitansi' => 'required|date'
        ]);

        $berkas = Berkas::findOrFail($id);
        $berkas->penerima_kwitansi = $request->penerima_kwitansi;
        $berkas->tgl_penyerahan_kwitansi = $request->tgl_penyerahan_kwitansi;
        $berkas->save();

        return redirect()->back()->with('success', 'Data penyerahan kwitansi berhasil disimpan!');
    }
}