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
    // Helper Fungsi Cek Hak Akses (Hanya Admin atau yang dicentang menunya yang bisa)
    private function cekAkses($hakAksesDibutuhkan)
    {
        $aksesMenu = is_array(auth()->user()->akses_menu) ? auth()->user()->akses_menu : json_decode(auth()->user()->akses_menu, true) ?? [];
        $isAdmin = optional(auth()->user()->jabatan)->is_admin;

        if (!$isAdmin && !in_array($hakAksesDibutuhkan, $aksesMenu)) {
            abort(403, "Akses Ditolak! Anda tidak memiliki izin untuk menu ($hakAksesDibutuhkan).");
        }

        return true;
    }

    public function index()
    {
        // 1. Pastikan user punya akses lihat WebGIS
        $this->cekAkses('WebGIS');

        // 2. Ambil data layer
        $layers = MapLayer::all();
        
        // 3. Cek apakah user ini boleh mengelola layer (dikirim ke view)
        $aksesMenu = is_array(auth()->user()->akses_menu) ? auth()->user()->akses_menu : json_decode(auth()->user()->akses_menu, true) ?? [];
        $bisaKelolaLayer = optional(auth()->user()->jabatan)->is_admin || in_array('Kelola Layer', $aksesMenu);

        // 4. Kirim variabel $layers dan $bisaKelolaLayer ke view
        return view('map.index', compact('layers', 'bisaKelolaLayer'));
    }

    public function import(Request $request)
    {
        // Pastikan user punya akses Kelola Layer
        $this->cekAkses('Kelola Layer');

        $request->validate([
            'nama_layer' => 'required|string',
            'file_zip' => 'required|mimes:zip|max:500000', // Max 500MB
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

        // Format nama tabel ke bentuk aman (huruf kecil & tanpa spasi)
        $tableName = 'layer_' . time() . '_' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $request->nama_layer));

        // Kredensial DB PostgreSQL Peta (dari .env)
        $dbHost = env('DB_PGSQL_HOST', '127.0.0.1');
        $dbPort = env('DB_PGSQL_PORT', '5432');
        $dbName = env('DB_PGSQL_DATABASE');
        $dbUser = env('DB_PGSQL_USERNAME');
        $dbPass = env('DB_PGSQL_PASSWORD');

        // COMMAND GDAL (ogr2ogr)
        $command = "ogr2ogr -f \"PostgreSQL\" PG:\"host=$dbHost port=$dbPort user=$dbUser dbname=$dbName password=$dbPass\" \"$shpFile\" -nln \"$tableName\" -nlt PROMOTE_TO_MULTI -lco GEOMETRY_NAME=geom -lco FID=id -overwrite -progress";
        
        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            Log::error("Import SHP Gagal", ['command' => $command, 'output' => $output]);
            return back()->with('error', 'Gagal memproses SHP. Pastikan GDAL terinstall di Environment Variables.');
        }

        // Simpan referensi ke database
        MapLayer::create([
            'nama_layer' => $request->nama_layer,
            'tabel_db' => $tableName,
            'warna' => $request->warna
        ]);

        // Hapus file temporary
        Storage::delete($zipPath);
        $this->deleteDirectory($extractPath);

        return back()->with('success', 'Data Peta SHP berhasil diimport ke database!');
    }

    public function getVectorTiles($layerId, $z, $x, $y)
    {
        // Tetap pastikan user punya akses lihat WebGIS
        $this->cekAkses('WebGIS');

        $layer = MapLayer::findOrFail($layerId);
        $table = $layer->tabel_db;

        // Query MVT PostGIS
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

        // Query dijalankan ke connection pgsql
        $result = DB::connection('pgsql')->select($query, [$z, $x, $y]);
        $tile = $result[0]->tile ?? null;

        if (!$tile) {
            return response('', 204); // No Content
        }

        return response($tile)->header('Content-Type', 'application/x-protobuf');
    }

    public function updateWarna(Request $request, $id)
    {
        $this->cekAkses('Kelola Layer');

        $request->validate(['warna' => 'required|string']);
        $layer = MapLayer::findOrFail($id);
        $layer->update(['warna' => $request->warna]);
        
        return back()->with('success', 'Warna layer berhasil diupdate.');
    }

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