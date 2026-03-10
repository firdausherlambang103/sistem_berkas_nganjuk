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
    // HELPER: Cek Hak Akses Sub-Menu
    // ========================================================
    private function cekAkses($hakAksesDibutuhkan)
    {
        $aksesMenu = is_array(auth()->user()->akses_menu) ? auth()->user()->akses_menu : json_decode(auth()->user()->akses_menu, true) ?? [];
        $isAdmin = optional(auth()->user()->jabatan)->is_admin;
        if (!$isAdmin && !in_array($hakAksesDibutuhkan, $aksesMenu)) {
            abort(403, "Akses Ditolak! Anda tidak memiliki izin untuk membuka menu ini.");
        }
        return true;
    }

    // ========================================================
    // HELPER: Ambil Layer yang Diizinkan Saja
    // ========================================================
    private function getAllowedLayers()
    {
        $user = auth()->user();
        $isAdmin = optional($user->jabatan)->is_admin;
        
        if ($isAdmin) {
            return MapLayer::orderBy('nama_layer')->get(); // Admin bisa lihat semua layer
        }

        $aksesLayer = is_array($user->akses_layer) ? $user->akses_layer : json_decode($user->akses_layer, true) ?? [];
        return MapLayer::whereIn('id', $aksesLayer)->orderBy('nama_layer')->get(); // User biasa hanya melihat layer yang dicentang
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
        $this->cekAkses('WebGIS'); // Cek Akses Menu Peta Utama
        
        $layers = $this->getAllowedLayers(); // Hanya load layer yang diizinkan

        $aksesMenu = is_array(auth()->user()->akses_menu) ? auth()->user()->akses_menu : json_decode(auth()->user()->akses_menu, true) ?? [];
        $bisaKelolaLayer = optional(auth()->user()->jabatan)->is_admin || in_array('Kelola Layer', $aksesMenu);
        
        return view('map.index', compact('layers', 'bisaKelolaLayer'));
    }

    // ========================================================
    // 2. API UNTUK LEAFLET (DENGAN FILTER HAK AKSES LAYER)
    // ========================================================
    public function apiData(Request $request)
    {
        if (!$request->has(['north', 'south', 'east', 'west', 'zoom'])) {
            return response()->json(['type' => 'FeatureCollection', 'features' => []]);
        }
        
        try {
            $n = number_format((float)$request->north, 8, '.', ''); 
            $s = number_format((float)$request->south, 8, '.', ''); 
            $e = number_format((float)$request->east, 8, '.', ''); 
            $w = number_format((float)$request->west, 8, '.', '');
            $zoom = (int) $request->zoom;
            
            $search = $request->input('search');
            $hak = $request->input('hak');
            
            // --- FILTER KEAMANAN LAYER ---
            $requestedLayers = $request->input('layers', []); 
            $user = auth()->user();
            $isAdmin = optional($user->jabatan)->is_admin;
            
            // Jika bukan admin, pastikan layer yang di-request benar-benar ada di daftar akses layernya
            if (!$isAdmin) {
                $aksesLayer = is_array($user->akses_layer) ? $user->akses_layer : json_decode($user->akses_layer, true) ?? [];
                $layerIds = array_intersect($requestedLayers, $aksesLayer);
            } else {
                $layerIds = $requestedLayers;
            }

            if (empty($layerIds)) {
                return response()->json(['type'=>'FeatureCollection', 'features'=>[]]);
            }
            // -----------------------------

            $polygonWKT = "SRID=4326;POLYGON(($w $s, $e $s, $e $n, $w $n, $w $s))";
            $keywords = $this->getHakKeywords($hak);
            
            $cacheKey = 'map_geojson_' . md5($polygonWKT . $zoom . $search . $hak . json_encode($layerIds));

            $features = Cache::remember($cacheKey, 60, function() use ($polygonWKT, $layerIds, $search, $keywords, $zoom) {
                
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

                if ($zoom < 13) {
                    $selectGeom = "ST_AsGeoJSON(ST_Simplify(geom, 0.00005))";
                } else {
                    $selectGeom = "ST_AsGeoJSON(geom)";
                }

                $data = $query->select('id', 'name', 'properties', 'layer_id', DB::raw("$selectGeom as geometry"))
                              ->limit(1500)
                              ->get();
                
                $formattedFeatures = [];
                $layersData = MapLayer::whereIn('id', $layerIds)->get()->keyBy('id');

                foreach ($data as $item) {
                    if (!$item->geometry) continue;
                    
                    $props = is_string($item->properties) ? json_decode($item->properties, true) : $item->properties;
                    $props = $props ?? [];
                    
                    $layer = $layersData->get($item->layer_id);
                    
                    $defaultColor = $layer->warna ?? '#3388ff';
                    $finalColor = $defaultColor;
                    $tipeLayer = $layer->tipe_layer ?? 'Standar';

                    $tipeHak = '';
                    $raw_data = $props['raw_data'] ?? $props;
                    
                    foreach ($raw_data as $key => $val) {
                        if (in_array(strtoupper($key), ['TIPEHAK', 'HAK', 'STATUS', 'JENIS_HAK'])) {
                            $tipeHak = strtolower(trim((string)$val));
                            break;
                        }
                    }

                    if ($tipeLayer === 'Utama' && $layer) {
                        if (str_contains($tipeHak, 'milik') || $tipeHak === 'hm') {
                            $finalColor = $layer->color_hm ?? '#28a745';
                        } elseif (str_contains($tipeHak, 'guna bangunan') || $tipeHak === 'hgb') {
                            $finalColor = $layer->color_hgb ?? '#ffc107';
                        } elseif (str_contains($tipeHak, 'pakai') || $tipeHak === 'hp') {
                            $finalColor = $layer->color_hp ?? '#17a2b8';
                        } elseif (str_contains($tipeHak, 'guna usaha') || $tipeHak === 'hgu') {
                            $finalColor = $layer->color_hgu ?? '#fd7e14';
                        } elseif (str_contains($tipeHak, 'wakaf')) {
                            $finalColor = $layer->color_wakaf ?? '#6f42c1';
                        } else {
                            $finalColor = '#cccccc'; 
                        }
                    }

                    $props['layer_color'] = $finalColor;
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
    // 3. STORE LAYER BARU
    // ========================================================
    public function storeLayer(Request $request)
    {
        $this->cekAkses('Kelola Layer');
        
        $request->validate([
            'nama_layer' => 'required|string',
            'tipe_layer' => 'required|string',
            'warna' => 'required|string'
        ]);

        MapLayer::create([
            'nama_layer' => $request->nama_layer,
            'tipe_layer' => $request->tipe_layer,
            'tabel_db' => 'spatial_features_' . time() . '_' . rand(10, 99),
            'warna' => $request->warna,
            'color_hm' => '#28a745',
            'color_hgb' => '#ffc107',
            'color_hp' => '#17a2b8',
            'color_hgu' => '#fd7e14',
            'color_wakaf' => '#6f42c1'
        ]);

        return back()->with('success', 'Layer "' . $request->nama_layer . '" berhasil dibuat! Silakan Import SHP ke layer ini.');
    }

    // ========================================================
    // 4. IMPORT SHP KE LAYER EXISTING
    // ========================================================
    public function import(Request $request)
    {
        $this->cekAkses('Kelola Layer');
        set_time_limit(0);              

        putenv('PROJ_LIB=');
        putenv('PROJ_DATA=');

        $request->validate([
            'layer_id' => 'required',
            'file_zip' => 'required|file|max:500000', 
        ], [
            'layer_id.required' => 'Silakan pilih Layer Tujuan terlebih dahulu.',
            'file_zip.required' => 'File SHP (.zip) wajib diunggah.',
            'file_zip.max' => 'Ukuran file terlalu besar (Maks 500MB).'
        ]);

        $file = $request->file('file_zip');
        
        if (strtolower($file->getClientOriginalExtension()) !== 'zip') {
            return back()->withErrors(['file_zip' => 'File yang diupload wajib memiliki format .zip!'])->withInput();
        }

        $layerId = $request->layer_id;
        $layer = MapLayer::find($layerId);
        if (!$layer) {
            return back()->withErrors(['layer_id' => 'Pilihan Layer Tujuan tidak ditemukan di dalam sistem database.'])->withInput();
        }

        $uniqueId = uniqid('shp_', true);
        $extractPath = storage_path('app/temp_shp/' . $uniqueId);
        
        try {
            if (!file_exists($extractPath)) mkdir($extractPath, 0777, true);
            $zip = new ZipArchive;
            if ($zip->open($file->getPathname()) === TRUE) { 
                $zip->extractTo($extractPath); $zip->close(); 
            } else { throw new \Exception('Gagal ekstrak file ZIP. File mungkin korup.'); }

            $shpFiles = [];
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($extractPath));
            foreach ($iterator as $info) {
                if ($info->isFile() && strtolower($info->getExtension()) === 'shp') $shpFiles[] = $info->getPathname();
            }
            if (empty($shpFiles)) throw new \Exception('File SHP utama (.shp) tidak ditemukan di dalam zip tersebut.');
            
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
                throw new \Exception("GDAL Engine Error: Gagal membaca file SHP. Pastikan isinya lengkap (.shp, .shx, .dbf, .prj).");
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
            
            DB::connection('pgsql')->insert($insertQuery, [$layer->id]);
            Schema::connection('pgsql')->dropIfExists($tempTableName);

        } catch (\Exception $e) {
            $this->deleteDirectory($extractPath);
            return back()->with('error', "Terjadi Kendala saat import: " . $e->getMessage());
        }

        $this->deleteDirectory($extractPath);
        return back()->with('success', 'Data SHP berhasil diimport dan dimasukkan ke dalam Layer "'. $layer->nama_layer .'"!');
    }

    // ========================================================
    // 5. MANUAL DRAW & CRUD ASET DARI PETA
    // ========================================================
    public function storeDraw(Request $request)
    {
        $this->cekAkses('Kelola Layer');
        
        try {
            $geometryJson = $request->geometry;
            $layerId = $request->layer_id;
            
            $rawData = [
                'NOMER_BERKAS' => $request->nomer_berkas ?? '', 
                'NIB' => $request->nib ?? '-',
                'TIPEHAK' => $request->tipehak ?? 'Tidak Diketahui',
                'LUAS' => $request->luas ?? 0,
                'PENGGUNAAN' => $request->penggunaan ?? '-',
                'KELURAHAN' => $request->kelurahan ?? '-',
                'KECAMATAN' => $request->kecamatan ?? '-',
                'KETERANGAN' => $request->keterangan ?? '-'
            ];

            $featureName = $request->nomer_berkas ?: ($request->nib ?? 'Aset Baru');

            DB::connection('pgsql')->table('spatial_features')->insert([
                'name' => $featureName,
                'layer_id' => $layerId,
                'properties' => json_encode([
                    'type' => 'Manual_Draw',
                    'raw_data' => $rawData
                ]),
                'geom' => DB::raw("ST_Force2D(ST_SetSRID(ST_GeomFromGeoJSON('$geometryJson'), 4326))"),
                'created_at' => now(), 
                'updated_at' => now()
            ]);

            return response()->json(['status' => 'success', 'message' => 'Aset bidang berhasil digambar dan disimpan!']);
        } catch (\Exception $e) { 
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500); 
        }
    }

    public function updateAsset(Request $request, $id)
    {
        $this->cekAkses('Kelola Layer');
        
        try {
            $asset = DB::connection('pgsql')->table('spatial_features')->where('id', $id)->first();
            if (!$asset) return response()->json(['status' => 'error', 'message' => 'Aset tidak ditemukan'], 404);

            $updateData = ['updated_at' => now()];

            if ($request->has('geometry')) {
                $geomJson = $request->geometry;
                $updateData['geom'] = DB::raw("ST_Force2D(ST_SetSRID(ST_GeomFromGeoJSON('$geomJson'), 4326))");
            }

            if ($request->has('is_attribute_update')) {
                $props = is_string($asset->properties) ? json_decode($asset->properties, true) : [];
                $raw = $props['raw_data'] ?? [];
                
                $raw['NOMER_BERKAS'] = $request->nomer_berkas ?? ''; 
                $raw['NIB'] = $request->nib;
                $raw['TIPEHAK'] = $request->tipehak;
                $raw['LUAS'] = $request->luas;
                $raw['PENGGUNAAN'] = $request->penggunaan;
                $raw['KELURAHAN'] = $request->kelurahan;
                $raw['KECAMATAN'] = $request->kecamatan;
                $raw['KETERANGAN'] = $request->keterangan;

                $props['raw_data'] = $raw;
                $updateData['properties'] = json_encode($props);
                $updateData['name'] = $request->nomer_berkas ?: ($request->nib ?? 'Aset');
                
                if($request->has('layer_id')) {
                    $updateData['layer_id'] = $request->layer_id;
                }
            }

            DB::connection('pgsql')->table('spatial_features')->where('id', $id)->update($updateData);

            return response()->json(['status' => 'success', 'message' => 'Data aset berhasil diperbarui!']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500); 
        }
    }

    public function showAsset($id) {
        $item = DB::connection('pgsql')->table('spatial_features')
            ->where('id', $id)
            ->select('id', 'name', 'layer_id', 'properties', DB::raw("ST_AsGeoJSON(geom) as geometry"))
            ->first();
            
        if (!$item) return response()->json(['error' => 'Data tidak ditemukan'], 404);
        
        if ($item->geometry) {
            $item->geometry = json_decode($item->geometry);
        }
        
        return response()->json($item);
    }
    
    public function destroyAsset($id) {
        $this->cekAkses('Kelola Layer');
        DB::connection('pgsql')->table('spatial_features')->where('id', $id)->delete(); 
        return response()->json(['status' => 'success', 'message' => 'Data dihapus!']); 
    }
    
    public function updateWarna(Request $request, $id) {
        $this->cekAkses('Kelola Layer');
        
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
    // 6. HALAMAN TABEL DATA ASET
    // ========================================================
    public function aset(Request $request)
    {
        $this->cekAkses('Data Aset'); // Cek Akses Menu Data Aset

        $layers = $this->getAllowedLayers(); // Hanya load layer yang diizinkan
        
        $selectedLayerId = $request->get('layer_id');
        $filterDesa = $request->get('desa');
        $filterSumber = $request->get('sumber');

        // Pastikan layer yang dipilih ada di dalam array $layers yang diizinkan
        $selectedLayer = null;
        if ($selectedLayerId) {
            $selectedLayer = $layers->firstWhere('id', $selectedLayerId);
        }
        if (!$selectedLayer) {
            $selectedLayer = $layers->first();
        }
        
        $features = [];
        $allDesaList = [];
        $paginator = null;

        if ($selectedLayer) {
            $allProps = DB::connection('pgsql')->table('spatial_features')
                ->where('layer_id', $selectedLayer->id)
                ->select('properties')
                ->get();

            $allDesa = [];
            foreach($allProps as $d) {
                $props = is_string($d->properties) ? json_decode($d->properties, true) : $d->properties;
                $raw = $props['raw_data'] ?? [];
                $rawLower = array_change_key_case($raw, CASE_LOWER);
                $desa = strtoupper(trim($rawLower['kelurahan'] ?? $rawLower['desa'] ?? 'TIDAK DIKETAHUI'));
                if ($desa !== 'TIDAK DIKETAHUI') {
                    $allDesa[$desa] = true;
                }
            }
            $allDesaList = array_keys($allDesa);
            sort($allDesaList);

            $query = DB::connection('pgsql')->table('spatial_features')
                ->where('layer_id', $selectedLayer->id)
                ->select('id', 'name', 'properties', 'layer_id'); 

            if ($filterSumber == 'Manual') {
                $query->where('properties', 'ILIKE', '%Manual%');
            } elseif ($filterSumber == 'Import') {
                $query->where('properties', 'ILIKE', '%Imported%');
            }

            if ($filterDesa) {
                $query->where('properties', 'ILIKE', '%' . $filterDesa . '%');
            }

            $paginator = $query->orderBy('id', 'desc')->paginate(50)->withQueryString();

            foreach($paginator->items() as $d) {
                $props = is_string($d->properties) ? json_decode($d->properties, true) : $d->properties;
                $raw = $props['raw_data'] ?? [];

                $jenisSumber = $props['type'] ?? 'Imported';
                $isManual = in_array(strtolower($jenisSumber), ['manual', 'manual_draw']);

                $rawLower = array_change_key_case($raw, CASE_LOWER);
                $desa = strtoupper(trim($rawLower['kelurahan'] ?? $rawLower['desa'] ?? 'TIDAK DIKETAHUI'));

                $features[] = (object) [
                    'id' => $d->id,
                    'name' => $d->name,
                    'nib' => $rawLower['nib'] ?? '-',
                    'tipe_hak' => strtoupper($rawLower['tipehak'] ?? $rawLower['hak'] ?? $rawLower['status'] ?? '-'),
                    'luas' => $rawLower['luastertul'] ?? $rawLower['luas'] ?? $rawLower['luaspeta'] ?? 0,
                    'penggunaan' => strtoupper($rawLower['penggunaan'] ?? '-'),
                    'desa' => $desa,
                    'kecamatan' => strtoupper($rawLower['kecamatan'] ?? $rawLower['kec'] ?? '-'),
                    'sumber' => $isManual ? 'Manual' : 'Import',
                    'layer_id' => $selectedLayer->id,
                    'raw_data' => json_encode($raw)
                ];
            }
        }
        
        return view('map.aset', compact('layers', 'selectedLayer', 'features', 'paginator', 'allDesaList', 'filterDesa', 'filterSumber'));
    }

    // ========================================================
    // 7. MASTER LAYER (ADMIN)
    // ========================================================
    public function masterLayer()
    {
        $this->cekAkses('Kelola Layer');
        $layers = MapLayer::all();
        return view('admin.layers.index', compact('layers'));
    }

    // ========================================================
    // 8. HAPUS MASTER LAYER & ISINYA
    // ========================================================
    public function destroyLayer($id)
    {
        $this->cekAkses('Kelola Layer');
        $layer = MapLayer::findOrFail($id);
        
        DB::connection('pgsql')->table('spatial_features')->where('layer_id', $layer->id)->delete();
        $layer->delete();

        return back()->with('success', 'Layer "' . $layer->nama_layer . '" dan seluruh aset di dalamnya berhasil dihapus!');
    }

    public function findBerkasLink(Request $request)
    {
        $noBerkas = $request->get('no_berkas');
        
        $berkas = DB::table('berkas')->where('nomer_berkas', $noBerkas)->first();
        
        if ($berkas) {
            return response()->json([
                'success' => true,
                'url' => route('berkas.show', $berkas->id)
            ]);
        }
        
        return response()->json(['success' => false]);
    }
}