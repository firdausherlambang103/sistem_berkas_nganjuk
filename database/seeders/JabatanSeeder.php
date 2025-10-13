<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Jabatan;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class JabatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Kosongkan tabel sebelum mengisi data baru
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Jabatan::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $jabatans = [
            ['nama_jabatan' => 'Kepala Kantor Pertanahan', 'is_admin' => true],
            ['nama_jabatan' => 'Loket Pelayanan Penyerahan', 'is_admin' => false],
            ['nama_jabatan' => 'Kepala Seksi Survei dan Pemetaan', 'is_admin' => false],
            ['nama_jabatan' => 'Koordinator Kelompok Substansi Pengukuran Dan Pemetaan Kadastral', 'is_admin' => false],
            ['nama_jabatan' => 'Pelaksana Kelompok Substansi Pengukuran Dan Pemetan Kadastral', 'is_admin' => false],
            ['nama_jabatan' => 'Petugas Pemetaan', 'is_admin' => false],
            ['nama_jabatan' => 'Petugas Ukur', 'is_admin' => false],
            ['nama_jabatan' => 'Kepala Seksi Penetapan Hak dan Pendaftaran', 'is_admin' => false],
            ['nama_jabatan' => 'Pelaksana Kelompok Substansi Penetapan Hak Tanah dan Ruang', 'is_admin' => false],
            ['nama_jabatan' => 'Pelaksana Kelompok Substansi Pendaftaran Tanah dan Ruang, Tanah Komunal dan Hubungan Kelembagaan', 'is_admin' => false],
            ['nama_jabatan' => 'Petugas Kontrol Pengumuman', 'is_admin' => false],
            ['nama_jabatan' => 'Tim Panitia', 'is_admin' => false],
            ['nama_jabatan' => 'Ketua Panitia/Ketua Peneliti Tanah', 'is_admin' => false],
            ['nama_jabatan' => 'Koordinator Kelompok Substansi Pendaftaran Tanah Dan Ruang, Tanah Komunal Dan Hubungan Kelembagaan', 'is_admin' => false],
            ['nama_jabatan' => 'Pelaksana Kelompok Substansi Pemeliharaan Data Hak Tanah dan Pembinaan PPAT', 'is_admin' => false],
            ['nama_jabatan' => 'Koordinator Kelompok Substansi Pemeliharaan Hak Tanah, Ruang Dan Pembinaan PPAT', 'is_admin' => false],
            ['nama_jabatan' => 'Kepala Seksi Penataan dan Pemberdayaan', 'is_admin' => false],
            ['nama_jabatan' => 'Pelaksana Kelompok Substansi Penatagunaan Tanah', 'is_admin' => false],
            ['nama_jabatan' => 'Petugas Loket Entri', 'is_admin' => false], // Menambahkan dari versi sebelumnya
            ['nama_jabatan' => 'Petugas Buku Tanah', 'is_admin' => false], // Menambahkan dari versi sebelumnya
            ['nama_jabatan' => 'Petugas Loket Penyerahan', 'is_admin' => false], // Menambahkan dari versi sebelumnya
        ];

        // Masukkan data baru ke database
        Jabatan::insert($jabatans);

        // Cari jabatan admin yang baru dibuat
        $adminJabatan = Jabatan::where('is_admin', true)->first();

        // Cari user pertama (yang sudah ada) dan pastikan dia menjadi admin
        // Ini untuk memastikan selalu ada setidaknya satu admin di sistem
        if ($adminJabatan) {
            $firstUser = User::first();
            if ($firstUser) {
                $firstUser->jabatan_id = $adminJabatan->id;
                $firstUser->is_approved = true; // Pastikan admin pertama selalu aktif
                $firstUser->save();
            }
        }
    }
}