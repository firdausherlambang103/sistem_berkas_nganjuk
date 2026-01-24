<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WaPlaceholder;
use Illuminate\Support\Facades\DB;

class WaPlaceholderSeeder extends Seeder
{
    public function run()
    {
        // Bersihkan tabel sebelum insert
        DB::table('wa_placeholders')->truncate();

        $placeholders = [
            // --- DATA UTAMA BERKAS ---
            [
                'placeholder' => '{nama_pemohon}',
                'deskripsi'   => 'nama_pemohon', // Kolom langsung
            ],
            [
                'placeholder' => '{nomer_berkas}',
                'deskripsi'   => 'nomer_berkas',
            ],
            [
                'placeholder' => '{tahun}',
                'deskripsi'   => 'tahun',
            ],
            [
                'placeholder' => '{status_berkas}',
                'deskripsi'   => 'status',
            ],
            [
                'placeholder' => '{nomer_hak}',
                'deskripsi'   => 'nomer_hak',
            ],

            // --- DATA RELASI / WILAYAH (Support Fallback) ---
            // Sistem akan mencoba mencari relasi 'dataDesa->nama_desa'.
            // Jika gagal (karena kolom desa hanya string), sistem otomatis mengambil string tersebut.
            [
                'placeholder' => '{nama_desa}',
                'deskripsi'   => 'desa.nama_desa', 
            ],
            [
                'placeholder' => '{nama_kecamatan}',
                'deskripsi'   => 'kecamatan.nama_kecamatan', 
            ],
            
            // --- DATA PROSES ---
            [
                'placeholder' => '{jenis_permohonan}',
                'deskripsi'   => 'jenisPermohonan.nama_jenis', // Relasi
            ],
            [
                'placeholder' => '{posisi_sekarang}',
                'deskripsi'   => 'posisiSekarang.name', // Nama petugas saat ini
            ],
            [
                'placeholder' => '{tgl_masuk}',
                'deskripsi'   => 'waktu_mulai_proses', // Akan otomatis diformat d-m-Y
            ],
        ];

        foreach ($placeholders as $p) {
            WaPlaceholder::create($p);
        }
    }
}