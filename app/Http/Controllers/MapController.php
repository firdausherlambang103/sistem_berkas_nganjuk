<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MapLayer;
use App\Models\SpatialFeature;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use ZipArchive;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

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
    // 2. API UNTUK LEAFLET (SUPER OPTIMIZED)
    // ========================================================
    public function apiData(Request $request)
    {
        if (!$request->has(['north', 'south', 'east', 'west', 'zoom'])) {
            return response()->json(['type' => 'FeatureCollection', 'features' => []]);
        }
        
        try {
            // FIX BUG BBOX LOKAL: Memaksa format titik (.)
            $n = number_format((float)$request->north, 8, '.', ''); 
            $s = number_format((float)$request->south, 8, '.', ''); 
            $e = number_format((float)$request->east, 8, '.', ''); 
            $w = number_format((float)$request->west, 8, '.', '');
            $zoom = (int) $request->zoom;
            
            $search = $request->input('search');
            $hak = $request->input('hak');
            $layerIds = $request->input('layers'); 

            if (empty($layerIds)) {
                return response()->json(['type'=>'FeatureCollection', 'features'=>[]]);
            }

            $polygonWKT = "SRID=4326;POLYGON(($w $s, $e $s, $e $n, $w $n, $w $s))";
            $keywords = $this->getHakKeywords($hak);
            
            // OPTIMASI: Cache API Key (Simpan hasil query selama 60 detik)
            $cacheKey = 'map_geojson_' . md5($polygonWKT . $zoom . $search . $hak . json_encode($layerIds));

            $features = Cache::remember($cacheKey, 60, function() use ($polygonWKT, $layerIds, $search, $keywords, $zoom) {
                
                // MENGGUNAKAN KONEKSI PGSQL KARENA SPATIAL DATA ADA DI POSTGRES
                $query = SpatialFeature::on('pgsql')
                    ->whereRaw("geom && ST_GeomFromEWKT(?)", [$polygonWKT])
                    ->whereIn('layer_id', $layerIds);
                
                if ($search) {
                    $term = '%' . $search . '%';
                    $query->where(function($q) use ($term) { 
                        $q->where('name', 'ILIKE', $term)->orWhereRaw("properties::text ILIKE ?", [$term]); 
                    });
                }
                
                if (!empty($keywords)) {
                    $query->where(function($q) use ($keywords) { 
                        foreach ($keywords as $word) {
                            $q->orWhereRaw("properties::text ILIKE ?", ['%' . $word . '%']); 
                        }
                    });
                }

                // OPTIMASI: ST_Simplify untuk Polygon saat Zoom Jauh
                if ($zoom < 13) {
                    $selectGeom = "ST_AsGeoJSON(ST_Simplify(geom, 0.00005))";
                } else {
                    $selectGeom = "ST_AsGeoJSON(geom)";
                }

                // OPTIMASI: Batasi maksimal 1500 data per request agar Browser aman
                $data = $query->select('id', 'name', 'properties', 'layer_id', DB::raw("$selectGeom as geometry"))
                              ->limit(1500)
                              ->get();
                
                $formattedFeatures = [];
                
                // Load semua layer ke memori untuk mengambil detail warna Hak
                $layersData = MapLayer::whereIn('id', $layerIds)->get()->keyBy('id');

                foreach ($data as $item) {
                    if (!$item->geometry) continue;
                    
                    $props = is_string($item->properties) ? json_decode($item->properties, true) : $item->properties;
                    $props = $props ?? [];
                    
                    $layer = $layersData->get($item->layer_id);
                    $defaultColor = $layer->warna ?? '#3388ff';
                    $finalColor = $defaultColor;

                    // Deteksi Jenis Hak dari properties
                    $tipeHak = '';
                    $raw_data = $props['raw_data'] ?? $props;
                    
                    foreach ($raw_data as $key => $val) {
                        if (in_array(strtoupper($key), ['TIPEHAK', 'HAK', 'STATUS', 'JENIS_HAK'])) {
                            $tipeHak = strtolower(trim((string)$val));
                            break;
                        }
                    }

                    // Tentukan warna berdasarkan jenis hak (Mendukung struktur web_gis_kediri)
                    if ($layer) {
                        if (str_contains($tipeHak, 'milik') || $tipeHak === 'hm') {
                            $finalColor = $layer->color_hm ?? $defaultColor;
                        } elseif (str_contains($tipeHak, 'guna bangunan') || $tipeHak === 'hgb') {
                            $finalColor = $layer->color_hgb ?? $defaultColor;
                        } elseif (str_contains($tipeHak, 'pakai') || $tipeHak === 'hp') {
                            $finalColor = $layer->color_hp ?? $defaultColor;
                        } elseif (str_contains($tipeHak, 'guna usaha') || $tipeHak === 'hgu') {
                            $finalColor = $layer->color_hgu ?? $defaultColor;
                        } elseif (str_contains($tipeHak, 'wakaf')) {
                            $finalColor = $layer->color_wakaf ?? $defaultColor;
                        }
                    }

                    $props['layer_color'] = $finalColor;
                    // Format tambahan agar frontend mudah membaca
                    $props['kategori_hak'] = strtoupper($tipeHak); 

                    $formattedFeatures[] = [
                        'type' => 'Feature', 
                        'geometry' => json_decode($item->geometry), 
                        'properties' => array_merge(['id'=>$item->id, 'name'=>$item->name], $props)
                    ];
                }
                return $formattedFeatures;
            });
            
            return response()->json(['type'=>'FeatureCollection', 'features'=>$features]);

        } catch (\Exception $e) { 
            return response()->json(['error'=>$e->getMessage()], 500); 
        }
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

        // BYPASS ENV LOKAL
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
                        'TIPEHAK' => $request->status, // TIPEHAK akan dirender khusus di apiData
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
        
        // Pastikan menyimpan semua parameter warna Hak agar terdeteksi beda-beda saat apiData() merender
        $updateData = [
            'warna' => $request->warna
        ];

        if ($request->has('color_hm')) $updateData['color_hm'] = $request->color_hm;
        if ($request->has('color_hgb')) $updateData['color_hgb'] = $request->color_hgb;
        if ($request->has('color_hgu')) $updateData['color_hgu'] = $request->color_hgu;
        if ($request->has('color_hp')) $updateData['color_hp'] = $request->color_hp;
        if ($request->has('color_wakaf')) $updateData['color_wakaf'] = $request->color_wakaf;

        MapLayer::where('id', $id)->update($updateData);
        
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