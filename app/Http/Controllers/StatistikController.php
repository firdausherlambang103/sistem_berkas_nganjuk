<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Berkas;
use App\Models\JenisPermohonan;
use Illuminate\Support\Facades\DB;

class StatistikController extends Controller
{
    public function index()
    {
        $tahun = date('Y');

        // 1. Data Grafik Berkas per Bulan (Tahun Berjalan)
        $berkasPerBulan = Berkas::select(DB::raw('EXTRACT(MONTH FROM created_at) as bulan'), DB::raw('count(*) as total'))
            ->whereYear('created_at', $tahun)
            ->groupBy('bulan')
            ->pluck('total', 'bulan')
            ->toArray();

        $bulanLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $dataBerkasBulan = [];
        for ($i = 1; $i <= 12; $i++) {
            // Cocokkan data bulan, jika kosong isi 0
            $dataBerkasBulan[] = $berkasPerBulan[$i] ?? 0;
        }

        // 2. Data Grafik Berdasarkan Status Berkas
        $statusBerkas = Berkas::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
        
        $labelStatus = array_keys($statusBerkas);
        $dataStatus = array_values($statusBerkas);

        // 3. Data Grafik Jenis Permohonan
        $jenisPermohonan = Berkas::select('jenis_permohonan_id', DB::raw('count(*) as total'))
            ->with('jenisPermohonan')
            ->groupBy('jenis_permohonan_id')
            ->get();

        $labelJenis = [];
        $dataJenis = [];
        foreach($jenisPermohonan as $jp) {
            $labelJenis[] = $jp->jenisPermohonan->nama_jenis ?? 'Lainnya'; // Sesuaikan dengan field nama di JenisPermohonan
            $dataJenis[] = $jp->total;
        }

        return view('statistik.index', compact(
            'bulanLabels', 'dataBerkasBulan', 
            'labelStatus', 'dataStatus', 
            'labelJenis', 'dataJenis', 
            'tahun'
        ));
    }
}