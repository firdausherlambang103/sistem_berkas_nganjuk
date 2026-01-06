<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WaPlaceholderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Bersihkan data lama agar tidak duplikat
        DB::table('wa_placeholders')->truncate();

        $placeholders = [
            // --- INFO DASAR BERKAS ---
            [
                'code' => '{nama_pemohon}',
                'description' => 'Nama lengkap pemohon sesuai berkas',
                'example' => 'Budi Santoso',
            ],
            [
                'code' => '{nomer_berkas}',
                'description' => 'Nomer registrasi berkas',
                'example' => '123/2023',
            ],
            [
                'code' => '{status}',
                'description' => 'Status proses berkas saat ini',
                'example' => 'Diproses / Selesai / Ditunda',
            ],
            [
                'code' => '{nomer_hak}',
                'description' => 'Nomer Hak Milik/Guna (jika ada)',
                'example' => '00123',
            ],
            [
                'code' => '{jenis_alas_hak}',
                'description' => 'Jenis alas hak (SHM, Letter C, dll)',
                'example' => 'Sertifikat Hak Milik',
            ],

            // --- INFO WILAYAH ---
            [
                'code' => '{desa}',
                'description' => 'Nama Desa lokasi tanah',
                'example' => 'Sukamaju',
            ],
            [
                'code' => '{kecamatan}',
                'description' => 'Nama Kecamatan lokasi tanah',
                'example' => 'Mojoanyar',
            ],

            // --- INFO PERMOHONAN ---
            [
                'code' => '{jenis_permohonan}',
                'description' => 'Nama jenis layanan permohonan',
                'example' => 'Pecah Bidang / Balik Nama',
            ],
            [
                'code' => '{tanggal_masuk}',
                'description' => 'Tanggal berkas didaftarkan (Format: DD-MM-YYYY)',
                'example' => '06-01-2026',
            ],
            [
                'code' => '{waktu_masuk}',
                'description' => 'Jam berkas didaftarkan (Format: HH:MM)',
                'example' => '09:30',
            ],

            // --- INFO TAMBAHAN ---
            [
                'code' => '{petugas}',
                'description' => 'Nama petugas yang memproses/mengirim pesan',
                'example' => 'Admin Loket 1',
            ],
            [
                'code' => '{penerima_kuasa}',
                'description' => 'Nama penerima kuasa (jika dikuasakan)',
                'example' => 'Notaris X / Bpk. Ahmad',
            ],
        ];

        DB::table('wa_placeholders')->insert($placeholders);
    }
}