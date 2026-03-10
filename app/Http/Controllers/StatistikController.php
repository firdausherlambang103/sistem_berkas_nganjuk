<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatistikController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil semua properties dari tabel spatial_features
        $features = DB::connection('pgsql')->table('spatial_features')->select('properties')->get();

        $totalLuas = 0;
        $totalSertipikat = 0;
        
        $kecamatanSet = [];
        $desaSet = [];

        $luasPerHak = [];
        $bidangPerPenggunaan = [];
        $asetPerDesa = [];

        // Parameter Filter dari URL
        $filterKecamatan = $request->get('kecamatan');
        $filterDesa = $request->get('desa');

        $allKecamatan = [];
        $allDesa = [];

        $filteredFeatures = [];

        // LOOPING 1: Kumpulkan daftar Kecamatan & Desa untuk Dropdown
        foreach ($features as $f) {
            $props = is_string($f->properties) ? json_decode($f->properties, true) : $f->properties;
            $raw = $props['raw_data'] ?? $props ?? [];
            
            // Ubah semua key array menjadi huruf kecil agar pencarian kebal terhadap perbedaan huruf SHP
            $rawLower = array_change_key_case($raw, CASE_LOWER);

            // Cari nilai Kecamatan & Desa (Ambil dari key: kecamatan, kelurahan, atau desa)
            $kec = strtoupper(trim($rawLower['kecamatan'] ?? $rawLower['kec'] ?? 'TIDAK DIKETAHUI'));
            $desa = strtoupper(trim($rawLower['kelurahan'] ?? $rawLower['desa'] ?? 'TIDAK DIKETAHUI'));

            // Masukkan ke array filter jika datanya valid
            if ($kec !== 'TIDAK DIKETAHUI') $allKecamatan[$kec] = true;
            if ($desa !== 'TIDAK DIKETAHUI') $allDesa[$desa] = true;

            // Jika sedang menggunakan filter, lewati data yang tidak cocok
            if ($filterKecamatan && $kec !== $filterKecamatan) continue;
            if ($filterDesa && $desa !== $filterDesa) continue;

            // Simpan rawLower untuk perhitungan statistik (agar tidak perlu array_change_key_case 2x)
            $filteredFeatures[] = $rawLower;
        }

        // LOOPING 2: Hitung Statistik hanya dari data yang lolos filter
        foreach ($filteredFeatures as $rawLower) {
            $totalSertipikat++; 

            // Hitung Luas (Prioritaskan luastertul, jika tidak ada pakai luaspeta)
            $luas = (float) ($rawLower['luastertul'] ?? $rawLower['luaspeta'] ?? $rawLower['luas'] ?? 0);
            $totalLuas += $luas;

            // Dapatkan kembali nama Kecamatan & Desa untuk Kartu
            $kec = strtoupper(trim($rawLower['kecamatan'] ?? $rawLower['kec'] ?? 'TIDAK DIKETAHUI'));
            $desa = strtoupper(trim($rawLower['kelurahan'] ?? $rawLower['desa'] ?? 'TIDAK DIKETAHUI'));
            
            $kecamatanSet[$kec] = true;
            $desaSet[$desa] = true;

            // Proporsi Luas Per Hak
            $tipeHak = strtoupper(trim($rawLower['tipehak'] ?? $rawLower['hak'] ?? $rawLower['status'] ?? 'TIDAK DIKETAHUI'));
            if (!isset($luasPerHak[$tipeHak])) $luasPerHak[$tipeHak] = 0;
            $luasPerHak[$tipeHak] += $luas;

            // Bidang Per Penggunaan
            $penggunaan = strtoupper(trim($rawLower['penggunaan'] ?? 'TIDAK DIKETAHUI'));
            if (!isset($bidangPerPenggunaan[$penggunaan])) $bidangPerPenggunaan[$penggunaan] = 0;
            $bidangPerPenggunaan[$penggunaan]++;

            // Sebaran Desa
            if (!isset($asetPerDesa[$desa])) $asetPerDesa[$desa] = 0;
            $asetPerDesa[$desa]++;
        }

        // Sorting grafik dari yang terbesar
        arsort($luasPerHak);
        arsort($bidangPerPenggunaan);
        arsort($asetPerDesa);

        // Sorting dropdown list Sesuai Abjad (A-Z)
        $allKecamatanList = array_keys($allKecamatan);
        $allDesaList = array_keys($allDesa);
        sort($allKecamatanList);
        sort($allDesaList);

        return view('statistik.index', [
            'totalSertipikat' => number_format($totalSertipikat, 0, ',', '.'),
            'totalLuas' => number_format($totalLuas, 2, ',', '.'),
            'totalKecamatan' => count(array_filter(array_keys($kecamatanSet), fn($v) => $v !== 'TIDAK DIKETAHUI')),
            'totalDesa' => count(array_filter(array_keys($desaSet), fn($v) => $v !== 'TIDAK DIKETAHUI')),
            
            'labelHak' => array_keys($luasPerHak),
            'dataHak' => array_values($luasPerHak),
            'labelPenggunaan' => array_keys($bidangPerPenggunaan),
            'dataPenggunaan' => array_values($bidangPerPenggunaan),
            'labelDesa' => array_keys($asetPerDesa),
            'dataDesa' => array_values($asetPerDesa),

            'allKecamatan' => $allKecamatanList, 
            'allDesa' => $allDesaList,           
            'filterKecamatan' => $filterKecamatan,
            'filterDesa' => $filterDesa,
        ]);
    }
}