<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MapLayer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use Illuminate\Support\Facades\Log;

class MapController extends Controller
{
    // 1. Tampilkan Halaman Peta & List Layer
    public function index()
    {
        // Mengambil data layer dari database PostgreSQL
        $layers = MapLayer::all();
        return view('map.index', compact('layers'));
    }

    // 2. Import SHP (ZIP) ke PostGIS via GDAL (ogr2ogr)
    public function import(Request $request)
    {
        $request->validate([
            'nama_layer' => 'required|string',
            'file_zip' => 'required|mimes:zip|max:500000', // max 500MB
            'warna' => 'required|string'
        ]);

        // Simpan file zip sementara
        $zipPath = $request->file('file_zip')->store('temp_shp');
        $extractPath = storage_path('app/temp_shp/extracted_' . time());
        
        // Ekstrak ZIP
        $zip = new ZipArchive;
        if ($zip->open(storage_path('app/' . $zipPath)) === TRUE) {
            $zip->extractTo($extractPath);
            $zip->close();
        } else {
            return back()->with('error', 'Gagal mengekstrak file ZIP.');
        }

        // Cari file .shp di dalam folder hasil ekstrak
        $shpFiles = glob($extractPath . '/*.shp');
        if (empty($shpFiles)) {
            return back()->with('error', 'File .shp tidak ditemukan di dalam ZIP.');
        }
        $shpFile = $shpFiles[0];

        // Generate nama tabel yang aman untuk database (hilangkan spasi/karakter aneh)
        $tableName = 'layer_' . time() . '_' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $request->nama_layer));

        // Ambil Kredensial DB PostgreSQL khusus Peta dari .env
        $dbHost = env('DB_PGSQL_HOST', '127.0.0.1');
        $dbPort = env('DB_PGSQL_PORT', '5432');
        $dbName = env('DB_PGSQL_DATABASE');
        $dbUser = env('DB_PGSQL_USERNAME');
        $dbPass = env('DB_PGSQL_PASSWORD');

        // COMMAND GDAL (ogr2ogr) untuk import SHP super cepat ke PostGIS
        $command = "ogr2ogr -f \"PostgreSQL\" PG:\"host=$dbHost port=$dbPort user=$dbUser dbname=$dbName password=$dbPass\" \"$shpFile\" -nln \"$tableName\" -nlt PROMOTE_TO_MULTI -lco GEOMETRY_NAME=geom -lco FID=id -overwrite -progress";
        
        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            Log::error("Import SHP Gagal", ['command' => $command, 'output' => $output]);
            return back()->with('error', 'Gagal memproses SHP menggunakan ogr2ogr. Pastikan GDAL sudah terinstall di Environment Variables Windows.');
        }

        // Simpan metadata ke tabel map_layers (Otomatis masuk ke PostgreSQL karena Model MapLayer sudah di-set pgsql)
        MapLayer::create([
            'nama_layer' => $request->nama_layer,
            'tabel_db' => $tableName,
            'warna' => $request->warna
        ]);

        // Bersihkan file temporary agar harddisk tidak penuh
        Storage::delete($zipPath);
        $this->deleteDirectory($extractPath);

        return back()->with('success', 'Data Peta SHP berhasil diimport ke database!');
    }

    // 3. GENERATE VECTOR TILES (MVT) DARI POSTGIS UNTUK DATA JUTAAN
    public function getVectorTiles($layerId, $z, $x, $y)
    {
        $layer = MapLayer::findOrFail($layerId);
        $table = $layer->tabel_db;

        // Query MVT canggih PostgreSQL (Sangat cepat memotong jutaan data)
        $query = "
            WITH bounds AS (
                SELECT ST_TileEnvelope(?, ?, ?) AS geom
            ),
            mvtgeom AS (
                SELECT ST_AsMVTGeom(ST_Transform(t.geom, 3857), bounds.geom) AS geom
                FROM $table t, bounds
                WHERE ST_Intersects(ST_Transform(t.geom, 3857), bounds.geom)
            )
            SELECT ST_AsMVT(mvtgeom, 'default') as tile FROM mvtgeom;
        ";

        // PENTING: Panggil DB::connection('pgsql')
        $result = DB::connection('pgsql')->select($query, [$z, $x, $y]);
        $tile = $result[0]->tile ?? null;

        if (!$tile) {
            return response('', 204); // No Content
        }

        return response($tile)->header('Content-Type', 'application/x-protobuf');
    }

    // 4. Update Warna Layer
    public function updateWarna(Request $request, $id)
    {
        $request->validate(['warna' => 'required|string']);
        $layer = MapLayer::findOrFail($id);
        $layer->update(['warna' => $request->warna]);
        
        return back()->with('success', 'Warna layer berhasil diupdate.');
    }

    // 5. Helper function untuk menghapus folder temporary
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