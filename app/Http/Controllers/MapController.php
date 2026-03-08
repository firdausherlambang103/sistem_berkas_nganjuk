<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MapLayer;
use App\Models\SpatialFeature;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use ZipArchive;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Illuminate\Support\Facades\Log;

class MapController extends Controller
{
    // ========================================================
    // HELPER: Cek Hak Akses
    // ========================================================
    private function cekAkses($hakAksesDibutuhkan)
    {
        $aksesMenu = is_array(auth()->user()->akses_menu) ? auth()->user()->akses_menu : json_decode(auth()->user()->akses_menu, true) ?? [];
        $isAdmin = optional(auth()->user()->jabatan)->is_admin;
        if (!$isAdmin && !in_array($hakAksesDibutuhkan, $aksesMenu)) {
            abort(403, "Akses Ditolak!");
        }
        return true;
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

    private function getHakKeywords($kode) {
        if (!$kode) return [];
        $kode = strtoupper($kode);
        $keywords = [$kode]; 
        if ($kode == 'HM') { $keywords[] = 'Hak Milik'; $keywords[] = 'Milik'; }
        if ($kode == 'HGB') { $keywords[] = 'Hak Guna Bangunan'; }
        if ($kode == 'HGU') { $keywords[] = 'Hak Guna Usaha'; } 
        if ($kode == 'HP') { $keywords[] = 'Hak Pakai'; }
        if ($kode == 'WAKAF') { $keywords[] = 'Wakaf'; }
        return $keywords;
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
    // 2. API UNTUK LEAFLET (GEOJSON MAP)
    // ========================================================
    public function apiData(Request $request)
    {
        if (!$request->has(['north', 'south', 'east', 'west', 'zoom'])) {
            return response()->json(['type' => 'FeatureCollection', 'features' => []]);
        }
        
        try {
            $n = (float) $request->north; 
            $s = (float) $request->south; 
            $e = (float) $request->east; 
            $w = (float) $request->west;
            $zoom = (int) $request->zoom;
            $search = $request->input('search');
            $hak = $request->input('hak');
            $layerIds = $request->input('layers'); 

            $polygonWKT = sprintf("SRID=4326;POLYGON((%F %F, %F %F, %F %F, %F %F, %F %F))", $w, $s, $e, $s, $e, $n, $w, $n, $w, $s);
            $features = [];
            $strategy = '';

            if (empty($layerIds)) {
                return response()->json(['type'=>'FeatureCollection', 'features'=>[], 'strategy'=>'empty']);
            }

            if ($zoom < 14 && empty($search) && empty($hak)) {
                $strategy = 'cluster';
                $gridSize = $zoom < 10 ? 0.05 : 0.005;
                $gridSizeStr = number_format($gridSize, 5, '.', ''); 
                
                $clusters = DB::connection('pgsql')->table('spatial_features')
                    ->whereIn('layer_id', $layerIds)
                    ->whereRaw("geom && ST_GeomFromEWKT(?)", [$polygonWKT])
                    ->select(DB::raw("COUNT(id) as total"), DB::raw("ST_AsGeoJSON(ST_Centroid(ST_Collect(geom::geometry))) as center"))
                    ->groupByRaw("ST_SnapToGrid(ST_Centroid(geom::geometry), $gridSizeStr)")
                    ->get();

                foreach ($clusters as $cluster) {
                    if (!$cluster->center) continue;
                    $features[] = [
                        'type' => 'Feature', 'geometry' => json_decode($cluster->center),
                        'properties' => ['type' => 'cluster', 'count' => $cluster->total]
                    ];
                }
            } 
            else {
                $query = SpatialFeature::query()->whereRaw("geom && ST_GeomFromEWKT(?)", [$polygonWKT])->whereIn('layer_id', $layerIds);
                
                if ($search) {
                    $term = '%' . $search . '%';
                    $query->where(function($q) use ($term) { $q->where('name', 'ILIKE', $term)->orWhereRaw("properties::text ILIKE ?", [$term]); });
                }
                
                if ($hak) {
                    $keywords = $this->getHakKeywords($hak);
                    $query->where(function($q) use ($keywords) { foreach ($keywords as $word) $q->orWhereRaw("properties::text ILIKE ?", ['%' . $word . '%']); });
                }

                $selectGeom = "ST_AsGeoJSON(geom)";
                $strategy = 'detail';

                $data = $query->select('id', 'name', 'properties', 'layer_id', DB::raw("$selectGeom as geometry"))->limit(3000)->get();
                
                foreach ($data as $item) {
                    if (!$item->geometry) continue;
                    $props = is_string($item->properties) ? json_decode($item->properties, true) : $item->properties;
                    $props = $props ?? [];
                    
                    $layerColor = '#3388ff';
                    $layerInfo = MapLayer::find($item->layer_id); 
                    if($layerInfo) $layerColor = $layerInfo->warna;

                    $props['layer_color'] = $layerColor; 

                    $features[] = [
                        'type' => 'Feature', 'geometry' => json_decode($item->geometry), 
                        'properties' => array_merge(['id'=>$item->id, 'name'=>$item->name], $props)
                    ];
                }
            }
            return response()->json(['type'=>'FeatureCollection', 'features'=>$features, 'strategy'=>$strategy]);
        } catch (\Exception $e) { return response()->json(['error'=>$e->getMessage()], 500); }
    }

    public function getLayerBounds($layerId)
    {
        $bounds = DB::connection('pgsql')->table('spatial_features')
            ->where('layer_id', $layerId)
            ->select(DB::raw("ST_AsGeoJSON(ST_Extent(geom)) as bbox"))
            ->first();

        if ($bounds && $bounds->bbox) {
            return response()->json(['success' => true, 'bbox' => json_decode($bounds->bbox)]);
        }
        return response()->json(['success' => false]);
    }

    // ========================================================
    // 3. IMPORT SHP (BYPASS GDAL PROJ)
    // ========================================================
    public function import(Request $request)
    {
        $this->cekAkses('Kelola Layer');
        set_time_limit(0);              

        // MEMAKSA PHP MENGHAPUS CACHE ENVIRONMENT WINDOWS YANG RUSAK
        putenv('PROJ_LIB=');
        putenv('PROJ_DATA=');

        $request->validate([
            'nama_layer' => 'required|string',
            'file_zip' => 'required|mimes:zip|max:500000', 
            'warna' => 'required|string'
        ]);

        $file = $request->file('file_zip');
        $layerName = $request->nama_layer;
        
        $layer = MapLayer::create([
            'nama_layer' => $layerName,
            'tabel_db' => 'spatial_features_' . time() . '_' . rand(10, 99),
            'warna' => $request->warna
        ]);
        $layerId = $layer->id;

        $uniqueId = uniqid('shp_', true);
        $extractPath = storage_path('app/temp_shp/' . $uniqueId);
        
        try {
            if (!file_exists($extractPath)) mkdir($extractPath, 0777, true);
            $zip = new ZipArchive;
            if ($zip->open($file->getPathname()) === TRUE) { 
                $zip->extractTo($extractPath); $zip->close(); 
            } else { throw new \Exception('Gagal ekstrak ZIP.'); }

            $shpFiles = [];
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($extractPath));
            foreach ($iterator as $info) {
                if ($info->isFile() && strtolower($info->getExtension()) === 'shp') $shpFiles[] = $info->getPathname();
            }
            if (empty($shpFiles)) throw new \Exception('File .shp tidak ditemukan.');
            
            $shpFile = $shpFiles[0];
            
            $tempTableName = 'temp_layer_' . time();
            
            $dbHost = env('DB_PGSQL_HOST', '127.0.0.1');
            $dbPort = env('DB_PGSQL_PORT', '5432');
            $dbName = env('DB_PGSQL_DATABASE', env('DB_DATABASE'));
            $dbUser = env('DB_PGSQL_USERNAME', env('DB_USERNAME'));
            $dbPass = env('DB_PGSQL_PASSWORD', env('DB_PASSWORD'));

            // [SOLUSI PAMUNGKAS]: 
            // 1. Membersihkan env di CMD via `set PROJ_LIB=`
            // 2. MENCABUT parameter `-t_srs EPSG:4326` agar GDAL tidak perlu memakai Proj.db sama sekali.
            $command = "set PROJ_LIB= && set PROJ_DATA= && ogr2ogr -f \"PostgreSQL\" PG:\"host=$dbHost port=$dbPort user=$dbUser dbname=$dbName password=$dbPass\" \"$shpFile\" -nln \"$tempTableName\" -nlt PROMOTE_TO_MULTI -makevalid -lco GEOMETRY_NAME=geom -lco FID=id -overwrite -progress 2>&1";
            exec($command, $output, $returnVar);

            if ($returnVar !== 0) {
                throw new \Exception("GDAL Error: " . implode(" ", $output));
            }

            $columns = Schema::connection('pgsql')->getColumnListing($tempTableName);
            
            $nameCol = 'id';
            $possibles = ['nib', 'nama', 'name', 'desa', 'kelurahan', 'pemilik'];
            foreach($columns as $c) {
                if(in_array(strtolower($c), $possibles)) {
                    $nameCol = $c; break;
                }
            }

            // [KONVERSI KORDINAT OLEH POSTGRESQL]
            // Karena GDAL dilarang memproses kordinat, kita serahkan konversinya (ST_Transform) secara langsung ke PostGIS
            $insertQuery = "
                INSERT INTO spatial_features (layer_id, name, properties, geom, created_at, updated_at)
                SELECT
                    ?,
                    COALESCE(\"$nameCol\"::text, 'Aset Baru'),
                    jsonb_build_object('type', 'Imported', 'raw_data', jsonb_strip_nulls(row_to_json(t)::jsonb - 'geom' - 'wkb_geometry' - 'id')),
                    ST_Force2D(ST_Transform(
                        CASE 
                            WHEN ST_SRID(geom) = 0 THEN ST_SetSRID(geom, 4326) 
                            ELSE geom 
                        END, 
                    4326)),
                    NOW(),
                    NOW()
                FROM \"$tempTableName\" t
                WHERE geom IS NOT NULL
            ";
            
            DB::connection('pgsql')->insert($insertQuery, [$layerId]);
            Schema::connection('pgsql')->dropIfExists($tempTableName);

        } catch (\Exception $e) {
            MapLayer::find($layerId)->delete();
            $this->deleteDirectory($extractPath);
            return back()->with('error', "Gagal memproses file: " . $e->getMessage());
        }

        $this->deleteDirectory($extractPath);
        return back()->with('success', 'Data SHP berhasil diimport dan dikonversi oleh PostGIS!');
    }

    // ========================================================
    // 4. MANUAL DRAW & CRUD
    // ========================================================
    public function storeDraw(Request $request)
    {
        $this->cekAkses('Kelola Layer');
        try {
            $geometryJson = $request->geometry;
            $layerId = $request->input('layer_id');
            
            DB::connection('pgsql')->table('spatial_features')->insert([
                'name' => $request->name,
                'layer_id' => $layerId,
                'properties' => json_encode([
                    'type' => 'Manual',
                    'raw_data' => [
                        'TIPEHAK' => $request->status, 
                        'KECAMATAN' => $request->kecamatan ?? '-', 
                        'KELURAHAN' => $request->desa ?? '-',
                        'PENGGUNAAN' => $request->description
                    ],
                    'color' => $request->color
                ]),
                'geom' => DB::raw("ST_Force2D(ST_SetSRID(ST_GeomFromGeoJSON('$geometryJson'), 4326))"),
                'created_at' => now(), 'updated_at' => now()
            ]);
            return response()->json(['status' => 'success', 'message' => 'Data bidang berhasil disimpan!']);
        } catch (\Exception $e) { 
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500); 
        }
    }

    public function showAsset($id) {
        $item = DB::connection('pgsql')->table('spatial_features')->where('id', $id)->first();
        if (!$item) return response()->json(['error' => 'Data tidak ditemukan'], 404);
        return response()->json($item);
    }
    
    public function destroyAsset($id) {
        $this->cekAkses('Kelola Layer');
        DB::connection('pgsql')->table('spatial_features')->where('id', $id)->delete(); 
        return response()->json(['status' => 'success', 'message' => 'Data dihapus!']); 
    }
    
    public function updateWarna(Request $request, $id) {
        $this->cekAkses('Kelola Layer');
        MapLayer::where('id', $id)->update(['warna' => $request->warna]);
        return response()->json(['status' => 'success']);
    }

    // ========================================================
    // 5. HALAMAN TABEL DATA ASET
    // ========================================================
    public function aset(Request $request)
    {
        $this->cekAkses('Data Aset');
        $layers = MapLayer::all();
        $selectedLayerId = $request->get('layer_id');
        $selectedLayer = $selectedLayerId ? MapLayer::find($selectedLayerId) : $layers->first();
        
        $features = [];
        if ($selectedLayer) {
            $data = DB::connection('pgsql')->table('spatial_features')->where('layer_id', $selectedLayer->id)->limit(1000)->get();
            foreach($data as $d) {
                $props = is_string($d->properties) ? json_decode($d->properties, true) : $d->properties;
                $raw = $props['raw_data'] ?? [];
                $features[] = (object) array_merge(['id' => $d->id, 'name' => $d->name], $raw);
            }
        }
        
        $columns = count($features) > 0 ? array_keys((array) $features[0]) : [];
        return view('map.aset', compact('layers', 'selectedLayer', 'features', 'columns'));
    }
}