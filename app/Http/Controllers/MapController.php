<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MapLayer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema; // Tambahan Facade Schema
use ZipArchive;
use Illuminate\Support\Facades\Log;

class MapController extends Controller
{
    // ========================================================
    // HELPER: Cek Hak Akses Menu
    // ========================================================
    private function cekAkses($hakAksesDibutuhkan)
    {
        $aksesMenu = is_array(auth()->user()->akses_menu) ? auth()->user()->akses_menu : json_decode(auth()->user()->akses_menu, true) ?? [];
        $isAdmin = optional(auth()->user()->jabatan)->is_admin;

        if (!$isAdmin && !in_array($hakAksesDibutuhkan, $aksesMenu)) {
            abort(403, "Akses Ditolak! Anda tidak memiliki izin untuk ($hakAksesDibutuhkan).");
        }
        return true;
    }

    // ========================================================
    // 1. TAMPILAN PETA UTAMA
    // ========================================================
    public function index()
    {
        $this->cekAkses('WebGIS');

        $layers = MapLayer::all();
        $aksesMenu = is_array(auth()->user()->akses_menu) ? auth()->user()->akses_menu : json_decode(auth()->user()->akses_menu, true) ?? [];
        $bisaKelolaLayer = optional(auth()->user()->jabatan)->is_admin || in_array('Kelola Layer', $aksesMenu);

        return view('map.index', compact('layers', 'bisaKelolaLayer'));
    }

    // ========================================================
    // 2. TAMPILAN DATA ASET (TABEL TABULAR)
    // ========================================================
    public function aset(Request $request)
    {
        $this->cekAkses('Data Aset');

        $layers = MapLayer::all();
        $selectedLayerId = $request->get('layer_id');
        $selectedLayer = $selectedLayerId ? MapLayer::find($selectedLayerId) : $layers->first();
        
        $features = collect();
        $columns = [];
        
        if ($selectedLayer) {
            $table = $selectedLayer->tabel_db;
            try {
                $featureData = DB::connection('pgsql')->table($table)->limit(5000)->get();
                if ($featureData->count() > 0) {
                    $features = $featureData;
                    $columns = array_keys((array) $featureData->first());
                    // Buang kolom geom dari tabel web agar tidak error panjang
                    $columns = array_diff($columns, ['geom', 'wkb_geometry']); 
                }
            } catch (\Exception $e) {
                Log::error("Gagal memuat atribut data aset: " . $e->getMessage());
            }
        }

        return view('map.aset', compact('layers', 'selectedLayer', 'features', 'columns'));
    }

    // ========================================================
    // 3. PROSES IMPORT SHP KE POSTGIS
    // ========================================================
    public function import(Request $request)
    {
        $this->cekAkses('Kelola Layer');

        $request->validate([
            'nama_layer' => 'required|string',
            'file_zip' => 'required|mimes:zip|max:500000', 
            'warna' => 'required|string'
        ]);

        $zipPath = $request->file('file_zip')->store('temp_shp');
        $extractPath = storage_path('app/temp_shp/extracted_' . time());
        
        $zip = new ZipArchive;
        if ($zip->open(storage_path('app/' . $zipPath)) === TRUE) {
            $zip->extractTo($extractPath);
            $zip->close();
        } else {
            return back()->with('error', 'Gagal mengekstrak file ZIP.');
        }

        $shpFiles = glob($extractPath . '/*.shp');
        if (empty($shpFiles)) {
            return back()->with('error', 'File .shp tidak ditemukan di dalam ZIP.');
        }
        $shpFile = $shpFiles[0];

        $tableName = 'layer_' . time() . '_' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $request->nama_layer));

        $dbHost = env('DB_PGSQL_HOST', '127.0.0.1');
        $dbPort = env('DB_PGSQL_PORT', '5432');
        $dbName = env('DB_PGSQL_DATABASE');
        $dbUser = env('DB_PGSQL_USERNAME');
        $dbPass = env('DB_PGSQL_PASSWORD');

        $command = "ogr2ogr -f \"PostgreSQL\" PG:\"host=$dbHost port=$dbPort user=$dbUser dbname=$dbName password=$dbPass\" \"$shpFile\" -nln \"$tableName\" -nlt PROMOTE_TO_MULTI -makevalid -t_srs EPSG:4326 -lco GEOMETRY_NAME=geom -lco FID=id -overwrite -progress 2>&1";
        
        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            $errorMessage = implode(" | ", $output);
            Log::error("Import SHP Gagal", ['command' => $command, 'output' => $output]);
            Storage::delete($zipPath);
            $this->deleteDirectory($extractPath);
            
            if (strpos($errorMessage, 'is not recognized') !== false || empty($output)) {
                return back()->with('error', 'Aplikasi GDAL (ogr2ogr) belum terinstal di server.');
            }
            return back()->with('error', 'Gagal memproses SHP: ' . $errorMessage);
        }

        // --- BUAT SPATIAL INDEX AGAR PETA SUPER CEPAT ---
        try {
            DB::connection('pgsql')->statement("CREATE INDEX IF NOT EXISTS {$tableName}_geom_idx ON \"$tableName\" USING GIST (geom)");
        } catch (\Exception $e) {
            Log::warning("Gagal membuat index spatial untuk $tableName");
        }

        MapLayer::create([
            'nama_layer' => $request->nama_layer,
            'tabel_db' => $tableName,
            'warna' => $request->warna
        ]);

        Storage::delete($zipPath);
        $this->deleteDirectory($extractPath);

        return back()->with('success', 'Data SHP berhasil diimport!');
    }

    // ========================================================
    // 4. MVT RENDERER (PERBAIKAN UTAMA DI SINI)
    // ========================================================
    public function getVectorTiles($layerId, $z, $x, $y)
    {
        $this->cekAkses('WebGIS');

        $layer = MapLayer::findOrFail($layerId);
        $table = $layer->tabel_db;

        // 1. WAJIB Cast ke Integer agar ST_TileEnvelope PostgreSQL tidak Error
        $z = (int) $z;
        $x = (int) $x;
        $y = (int) $y;

        try {
            // 2. Ambil semua nama kolom tabel, lalu BUANG kolom geom.
            // Ini untuk menghindari error "kolom duplikat" pada fungsi MVT
            $columns = Schema::connection('pgsql')->getColumnListing($table);
            $attributeCols = array_diff($columns, ['geom', 'wkb_geometry']); 
            
            $selectAttributes = '';
            if (!empty($attributeCols)) {
                // Bungkus kolom dengan tanda kutip " agar aman jika ada spasi/huruf besar
                $quotedCols = array_map(function($col) { return "t.\"$col\""; }, $attributeCols);
                $selectAttributes = ", " . implode(', ', $quotedCols);
            }

            // 3. Query MVT yang sudah bersih
            $query = "
                WITH bounds AS (
                    SELECT ST_TileEnvelope(?, ?, ?) AS bounds_geom
                ),
                mvtgeom AS (
                    SELECT ST_AsMVTGeom(ST_Transform(ST_SetSRID(t.geom, 4326), 3857), bounds.bounds_geom) AS geom
                           $selectAttributes
                    FROM \"$table\" t, bounds
                    WHERE ST_Intersects(ST_Transform(ST_SetSRID(t.geom, 4326), 3857), bounds.bounds_geom)
                )
                SELECT ST_AsMVT(mvtgeom, 'default') as tile FROM mvtgeom;
            ";

            $result = DB::connection('pgsql')->select($query, [$z, $x, $y]);
            $tile = $result[0]->tile ?? null;

            if (!$tile) {
                return response('', 204); 
            }

            return response($tile)->header('Content-Type', 'application/x-protobuf')
                                 ->header('Access-Control-Allow-Origin', '*'); // Pastikan CORS diizinkan

        } catch (\Exception $e) {
            Log::error("MVT Error pada Layer {$layer->nama_layer}: " . $e->getMessage());
            return response('', 204);
        }
    }

    // ========================================================
    // 5. UPDATE WARNA
    // ========================================================
    public function updateWarna(Request $request, $id)
    {
        $this->cekAkses('Kelola Layer');
        $request->validate(['warna' => 'required|string']);
        $layer = MapLayer::findOrFail($id);
        $layer->update(['warna' => $request->warna]);
        return back()->with('success', 'Warna layer diupdate.');
    }

    // ========================================================
    // 6. HELPER HAPUS FOLDER
    // ========================================================
    private function deleteDirectory($dir) {
        if (!file_exists($dir)) return true;
        if (!is_dir($dir)) return unlink($dir);
        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') continue;
            if (!$this->deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) return false;
        }
        return rmdir($dir);
    }
}