<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MapLayer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class MapController extends Controller
{
    // 1. Tampilkan Halaman Peta & List Layer
    public function index()
    {
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

        $zipPath = $request->file('file_zip')->store('temp_shp');
        $extractPath = storage_path('app/temp_shp/extracted_' . time());
        
        // Ekstrak ZIP
        $zip = new ZipArchive;
        if ($zip->open(storage_path('app/' . $zipPath)) === TRUE) {
            $zip->extractTo($extractPath);
            $zip->close();
        } else {
            return back()->with('error', 'Gagal mengekstrak ZIP.');
        }

        // Cari file .shp di dalam folder ekstrak
        $shpFile = glob($extractPath . '/*.shp')[0] ?? null;
        if (!$shpFile) {
            return back()->with('error', 'File .shp tidak ditemukan di dalam ZIP.');
        }

        // Generate nama tabel yang aman untuk database
        $tableName = 'layer_' . time() . '_' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $request->nama_layer));

        // Kredensial DB PostgreSQL Anda
        $dbHost = env('DB_HOST');
        $dbName = env('DB_DATABASE');
        $dbUser = env('DB_USERNAME');
        $dbPass = env('DB_PASSWORD');

        // COMMAND GDAL (ogr2ogr) untuk import SHP super cepat ke PostGIS
        // Pastikan GDAL sudah terinstall di server/Windows Anda
        $command = "ogr2ogr -f \"PostgreSQL\" PG:\"host=$dbHost user=$dbUser dbname=$dbName password=$dbPass\" \"$shpFile\" -nln \"$tableName\" -nlt PROMOTE_TO_MULTI -lco GEOMETRY_NAME=geom -lco FID=id -overwrite -progress";
        
        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            return back()->with('error', 'Gagal memproses SHP menggunakan ogr2ogr. Pastikan GDAL terinstall.');
        }

        // Simpan ke Database
        MapLayer::create([
            'nama_layer' => $request->nama_layer,
            'tabel_db' => $tableName,
            'warna' => $request->warna
        ]);

        // Bersihkan file temporary
        Storage::delete($zipPath);
        exec("rm -rf " . escapeshellarg($extractPath));

        return back()->with('success', 'Data SHP berhasil diimport ke database!');
    }

    // 3. GENERATE VECTOR TILES (MVT) DARI POSTGIS UNTUK DATA JUTAAN
    public function getVectorTiles($layerId, $z, $x, $y)
    {
        $layer = MapLayer::findOrFail($layerId);
        $table = $layer->tabel_db;

        // Query MVT canggih PostgreSQL (Sangat cepat untuk jutaan data)
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

        $result = DB::select($query, [$z, $x, $y]);
        $tile = $result[0]->tile ?? null;

        if (!$tile) {
            return response('', 204); // No Content
        }

        return response($tile)->header('Content-Type', 'application/x-protobuf');
    }

    // 4. Update Warna Layer
    public function updateWarna(Request $request, $id)
    {
        $layer = MapLayer::findOrFail($id);
        $layer->update(['warna' => $request->warna]);
        return back()->with('success', 'Warna layer berhasil diupdate.');
    }
}