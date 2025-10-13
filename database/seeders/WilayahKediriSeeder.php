<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kecamatan;
use App\Models\Desa;
use Illuminate\Support\Facades\DB;

class WilayahKediriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Kosongkan tabel sebelum mengisi data baru untuk menghindari duplikasi
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Desa::truncate();
        Kecamatan::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $data = [
            "Badas" => ["Badas", "Bringin", "Canggu", "Krecek", "Lamong", "Sekoto", "Tunglur"],
            "Banyakan" => ["Banyakan", "Jabon", "Jatirejo", "Manyaran", "Maron", "Ngasinan", "Parang", "Sendang", "Tiron"],
            "Gampengrejo" => ["Gampeng", "Jongbiru", "Kalibelo", "Kepuhrejo", "Ngebrak", "Plosorejo", "Putih", "Sambirejo", "Sumberejo", "Turus", "Wanengpaten"],
            "Grogol" => ["Bakalan", "Cerme", "Datengan", "Gambiran", "Grogol", "Kalipang", "Sonorejo", "Sumberejo", "Wonoasri"],
            "Gurah" => ["Adan-adan", "Bangkok", "Banyuanyar", "Besuk", "Blimbing", "Bogem", "Gabru", "Gamol", "Gayam", "Gurah", "Kerkep", "Kranggan", "Ngasem", "Nglumbang", "Sukorejo", "Sumbercangkring", "Tambakrejo", "Tiru Kidul", "Tiru Lor", "Turus", "Wonojoyo"],
            "Kandangan" => ["Banaran", "Bukur", "Jerukwangi", "Jlumbang", "Kandangan", "Karangtengah", "Kasreman", "Kemiri", "Klampisan", "Medowo", "Mlancu", "Sumberejo"],
            "Kandat" => ["Blabak", "Cendono", "Gadang", "Kandat", "Karangrejo", "Ngletih", "Ngreco", "Pule", "Purworejo", "Ringinsari", "Selosari", "Tegalan"],
            "Kayen Kidul" => ["Bangsongan", "Bayas", "Jambu", "Kayen Kidul", "Mukuh", "Nanggungan", "Padangan", "Sambi", "Sekaran", "Senden", "Sukoharjo", "Tulungrejo"],
            "Kepung" => ["Besowo", "Brumbung", "Damarwulan", "Gadungan", "Kampungbaru", "Kebonrejo", "Keling", "Kepung", "Krenceng", "Siman"],
            "Kras" => ["Banjaranyar", "Bendosari", "Butuh", "Jabang", "Jambean", "Kanigoro", "Karangtalun", "Kras", "Krinjing", "Mojoayu", "Nyawangan", "Pelas", "Purwodadi", "Rejomulyo", "Setonorejo", "Sumberejo"],
            "Kunjang" => ["Balongjeruk", "Dungus", "Juwet", "Kapas", "Kapi", "Klepek", "Kunjang", "Pakis", "Parelor", "Sumberejo", "Tengger Kidul", "Wonorejo"],
            "Mojo" => ["Blimbing", "Jugo", "Kedawung", "Keniten", "Kerep", "Kranggan", "Maesan", "Mojo", "Mondo", "Ngadi", "Ngetrep", "Pamongan", "Petok", "Ploso", "Ponggok", "Sukoanyar", "Surat", "Tambibendo", "Temon", "Petungroto"],
            "Ngadiluwih" => ["Badal", "Badal Pandean", "Banjarejo", "Bedug", "Branggahan", "Dukuh", "Mangunrejo", "Ngadiluwih", "Purwokerto", "Rembang", "Rembang Kepuh", "Seketi", "Slumbung", "Tales", "Wonosari"],
            "Ngancar" => ["Babadan", "Bedali", "Jagul", "Kunjang", "Manggis", "Margourip", "Ngancar", "Pandantoyo", "Sempu", "Sugihwaras"],
            "Ngasem" => ["Bangle", "Doko", "Gogorante", "Karangrejo", "Kwadungan", "Nambaan", "Ngasem", "Paron", "Sukorejo", "Sumberejo", "Toyoresmi", "Tugurejo"],
            "Pagu" => ["Bendo", "Bulupasar", "Jagung", "Kambingan", "Menang", "Pagu", "Semanding", "Semen", "Sitimerto", "Tanjung", "Tengger Lor", "Wates", "Wonorejo"],
            "Papar" => ["Dawuhan Kidul", "Janti", "Kepuh", "Kwaron", "Maduretno", "Minggiran", "Ngampel", "Papar", "Pehkulon", "Pehwetan", "Puhjajar", "Purwotengah", "Srikaton", "Sukomoro", "Tanon"],
            "Pare" => ["Bendo", "Darungan", "Gedangsewu", "Pare", "Pelem", "Sambirejo", "Sidorejo", "Sumberbendo", "Tertek", "Tulungrejo"],
            "Plemahan" => ["Banjarejo", "Bogokidul", "Kayen Lor", "Mejono", "Mojoayu", "Mojokerep", "Ngino", "Payaman", "Plemahan", "Puhjarak", "Ringinpitu", "Sebet", "Sidowarek", "Sukoharjo", "Tegowangi", "Wonokerto", "Wonokromo"],
            "Plosoklaten" => ["Brenggolo", "Donganti", "Gondang", "Jarak", "Kawedusan", "Klanderan", "Panjer", "Ploso Kidul", "Ploso Lor", "Plosoklaten", "Pranggang", "Punjul", "Sepawon", "Sumberagung", "Wonorejo Trisulo"],
            "Puncu" => ["Asmorobangun", "Gadungan", "Manggis", "Puncu", "Satak", "Sidomulyo", "Watugede", "Wonotengah"],
            "Purwoasri" => ["Belor", "Blawe", "Bulu", "Cengkok", "Dawuhan", "Dayu", "Jantok", "Karangpakis", "Kempleng", "Ketawang", "Klampitan", "Merjoyo", "Mranggen", "Muneng", "Pandansari", "Pesing", "Purwoasri", "Purwodadi", "Sidomulyo", "Sumberjo", "Tlogo", "Wonotirto", "Woromarto"],
            "Ringinrejo" => ["Batuaji", "Bedug", "Dawung", "Deyeng", "Jemekan", "Nambakan", "Purwodadi", "Ringinrejo", "Sambi", "Selodono", "Srikaton", "Susuhbango"],
            "Semen" => ["Bobang", "Bulu", "Joho", "Kanyoran", "Kedak", "Pagung", "Puhrubuh", "Puhsarang", "Selopanggung", "Semen", "Sidomulyo", "Titik"],
            "Tarokan" => ["Blimbing", "Bulusari", "Cengkok", "Jati", "Kaliboto", "Kalianyar", "Kedungsari", "Kerep", "Maron", "Sumberduren", "Tarokan"],
            "Wates" => ["Duwet", "Gadungan", "Jajar", "Janti", "Joho", "Karanganyar", "Plaosan", "Pojok", "Segaran", "Silir", "Sidomulyo", "Sumberagung", "Tawang", "Tempurejo", "Tunge", "Wates", "Wonorejo"],
        ];

        foreach ($data as $nama_kecamatan => $desas) {
            $kecamatan = Kecamatan::create(['nama_kecamatan' => $nama_kecamatan]);
            foreach ($desas as $nama_desa) {
                Desa::create([
                    'kecamatan_id' => $kecamatan->id,
                    'nama_desa' => $nama_desa
                ]);
            }
        }
    }
}