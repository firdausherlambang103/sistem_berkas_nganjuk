<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SensusWakaf;
use Illuminate\Support\Facades\File;

class SensusWakafSeeder extends Seeder
{
    public function run(): void
    {
        // Pastikan file CSV ada di database/seeders/data_wakaf.csv
        $csvFile = database_path('seeders/data_wakaf.csv'); 

        if (!File::exists($csvFile)) {
            $this->command->error("File CSV tidak ditemukan di: $csvFile");
            return;
        }

        $data = array_map('str_getcsv', file($csvFile));
        $header = array_shift($data); // Ambil baris pertama sebagai header

        foreach ($data as $row) {
            // Mapping data sesuai urutan kolom CSV Anda
            // CSV Header: _REC_TIME,gen_LATI,gen_LONG,Penggunaan,Pengenal,Status_T_1,Affilias_1,...

            if (count($row) < 13) continue; // Skip baris kosong/rusak

            // Bersihkan Koordinat (Hapus kutip dan ganti koma jadi titik)
            $lat = str_replace(['"', ','], ['', '.'], $row[1]);
            $long = str_replace(['"', ','], ['', '.'], $row[2]);

            SensusWakaf::create([
                'rec_time' => $row[0],
                'latitude' => is_numeric($lat) ? $lat : null,
                'longitude' => is_numeric($long) ? $long : null,
                'penggunaan' => $row[3],
                'pengenal' => $row[4],
                'status_tanah' => $row[5],
                'afiliasi' => $row[6],
                'kecamatan' => $row[11] ?? null,
                'desa' => $row[12] ?? null,
            ]);
        }
    }
}