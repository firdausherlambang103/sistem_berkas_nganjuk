<?php

namespace App\Http\Controllers;

use App\Models\SensusWakaf;
use Illuminate\Http\Request;

class SensusWakafController extends Controller
{
    public function index(Request $request)
    {
        // Query Dasar
        $query = SensusWakaf::query();

        // 1. Filter Berdasarkan Header CSV (Jika ada input)
        if ($request->filled('pengenal')) {
            $query->where('pengenal', 'like', '%' . $request->pengenal . '%');
        }
        if ($request->filled('penggunaan')) {
            $query->where('penggunaan', $request->penggunaan);
        }
        if ($request->filled('kecamatan')) {
            $query->where('kecamatan', 'like', '%' . $request->kecamatan . '%');
        }
        if ($request->filled('desa')) {
            $query->where('desa', 'like', '%' . $request->desa . '%');
        }
        if ($request->filled('afiliasi')) {
            $query->where('afiliasi', $request->afiliasi);
        }

        // 2. Ambil data untuk Tabel (List) dengan Pagination (10 per halaman biar cepat)
        $dataWakaf = $query->latest()->paginate(10)->withQueryString();

        // Ambil opsi unik untuk dropdown filter
        $listPenggunaan = SensusWakaf::select('penggunaan')->distinct()->pluck('penggunaan');
        $listAfiliasi = SensusWakaf::select('afiliasi')->distinct()->pluck('afiliasi');

        return view('admin.sensus-wakaf.index', compact('dataWakaf', 'listPenggunaan', 'listAfiliasi'));
    }

    public function getMapData()
    {
        // OPTIMASI: Hanya ambil kolom yang dibutuhkan peta untuk mengurangi beban load
        // Kita gunakan select() agar tidak menarik kolom timestamp atau lainnya yang tidak perlu di peta
        $data = SensusWakaf::select('id', 'latitude', 'longitude', 'pengenal', 'penggunaan', 'status_tanah', 'afiliasi', 'desa', 'kecamatan')
                    ->whereNotNull('latitude')
                    ->whereNotNull('longitude')
                    ->get();
        
        return response()->json($data);
    }
}
