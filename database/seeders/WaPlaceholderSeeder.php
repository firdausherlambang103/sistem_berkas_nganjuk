<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WaPlaceholder;
use Illuminate\Support\Facades\DB;

class WaPlaceholderSeeder extends Seeder
{
    public function run()
    {
        // Kosongkan tabel dulu agar bersih
        DB::table('wa_placeholders')->truncate();

        $placeholders = [
            // DATA LANGSUNG DARI TABEL BERKAS
            [
                'placeholder' => '{nama_pemohon}',
                'deskripsi'   => 'nama_pemohon', 
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

            // DATA DARI RELASI (PASTIKAN NAMA RELASI DI MODEL BERKAS SUDAH DIBUAT)
            [
                'placeholder' => '{jenis_permohonan}',
                'deskripsi'   => 'jenisPermohonan.nama_jenis', // Relasi: jenisPermohonan, Kolom: nama_jenis
            ],
            [
                'placeholder' => '{nama_desa}',
                'deskripsi'   => 'desa.nama_desa', // Relasi: desa, Kolom: nama_desa
            ],
            [
                'placeholder' => '{nama_kecamatan}',
                'deskripsi'   => 'kecamatan.nama_kecamatan', // Relasi: kecamatan, Kolom: nama_kecamatan
            ],
            [
                'placeholder' => '{posisi_sekarang}',
                'deskripsi'   => 'posisiSekarang.name', // Relasi: posisiSekarang (User), Kolom: name
            ],
            [
                'placeholder' => '{petugas_ukur}',
                'deskripsi'   => 'petugasUkur.nama_petugas', // Sesuaikan dengan kolom di tabel petugas_ukur
            ],
        ];

        foreach ($placeholders as $p) {
            WaPlaceholder::create($p);
        }
    }
}