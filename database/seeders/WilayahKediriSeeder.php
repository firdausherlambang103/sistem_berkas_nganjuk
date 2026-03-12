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

        // Data Wilayah Kabupaten Nganjuk
        $data = [
            "Nganjuk" => ["Bagor", "Balongpacul", "Begadung", "Bondowoso", "Ganungkidul", "Jatirejo", "Kramat", "Kapas", "Kauman", "Mangundikaran", "Payaman", "Ploso", "Ringinanom", "Werungotok"],
            "Bagor" => ["Bagor", "Banarankulon", "Banaranwetan", "Buduran", "Gandu", "Girirejo", "Karangtengah", "Kendalkerep", "Kerep Kidul", "Ngumpul", "Parungrejo", "Pesudukuh", "Petampon", "Selorejo", "Sucho"],
            "Baron" => ["Baron", "Garuman", "Gebangkerep", "Jambi", "Jatigreges", "Katerban", "Kemaduh", "Kemlokolegi", "Mabung", "Sambiroto", "Waung"],
            "Berbek" => ["Berbek", "Balongrejo", "Bendungrejo", "Bulurejo", "Cepoko", "Grojogan", "Kacangan", "Kalisobo", "Maguan", "Mlandangan", "Ngrawan", "Patranrejo", "Salamrojo", "Semare", "Sendangbumen", "Sengkut", "Sukoiber", "Tiripan"],
            "Gondang" => ["Gondang", "Balonggebang", "Campur", "Gegersuko", "Glonggong", "Jatigreges", "Karangsemi", "Ketawang", "Ngujung", "Pandean", "Pujon", "Sanggrahan", "Senggowar", "Senjayan", "Sumberagung", "Sumberjo"],
            "Jatikalen" => ["Jatikalen", "Begendeng", "Dawuhan", "Dlururejo", "Gondanglegi", "Lumbangkerep", "Munung", "Ngrombot", "Perning", "Pule", "Pulowetan"],
            "Kertosono" => ["Banaran", "Drenges", "Kertosono", "Kudu", "Kutorejo", "Lambangkuning", "Nglawak", "Pandantoyo", "Pelem", "Tanjung", "Tembarak", "Yuwanamarta"],
            "Lengkong" => ["Lengkong", "Banjardowo", "Bareng", "Bukit", "Jegreg", "Kedungmulyo", "Ketandan", "Ngadiboyo", "Ngepung", "Pinggir", "Prayungan", "Sawahan", "Sumberkepuh", "Sumbersono"],
            "Loceret" => ["Loceret", "Bajulan", "Candirejo", "Gejagan", "Genjeng", "Gayam", "Jatirejo", "Karas", "Kenep", "Kwangsan", "Macanan", "Mungkung", "Ngepeh", "Pacing", "Patihan", "Putukrejo", "Sombron", "Sukorejo", "Tekung", "Tanjungrejo"],
            "Ngetos" => ["Ngetos", "Blongko", "Kepel", "Kuncir", "Mojoduwur", "Oro-oro Ombo", "Suru", "Sukoanyar", "Wonotirto"],
            "Ngluyu" => ["Ngluyu", "Bajang", "Gampeng", "Lengkong Lor", "Pugeran", "Sugihwaras"],
            "Ngronggot" => ["Ngronggot", "Banjarsari", "Betet", "Cengkok", "Dadapan", "Juwet", "Kalianyar", "Kaloran", "Kelutan", "Klurahan", "Mojokendil", "Pandanrejo", "Tanjungkalang"],
            "Pace" => ["Pace", "Banyak", "Batre", "Bodag", "Cerme", "Gampeng", "Gemenggeng", "Gondang", "Jatigreges", "Jetis", "Joho", "Kecubung", "Kepuh", "Mlandangan", "Pacekulon", "Pacewetan", "Plosoharjo", "Sanan"],
            "Patianrowo" => ["Patianrowo", "Babadan", "Bukit", "Lestari", "Ngepung", "Ngrombot", "Pecuk", "Pisang", "Rowomarto", "Talun", "Trowulo"],
            "Prambon" => ["Prambon", "Baleturi", "Bandung", "Gondanglegi", "Kurungrejo", "Ngetos", "Rowoharjo", "Sanggrahan", "Singkalanyar", "Sugihwaras", "Sukoanyar", "Tanjungtani", "Tegaron", "Watudandang"],
            "Rejoso" => ["Rejoso", "Banjarejo", "Banyuasri", "Gempol", "Jatirejo", "Klurahan", "Mlorah", "Mojorembun", "Musir Kidul", "Musir Lor", "Ngadiboyo", "Ngaren", "Puhkerep", "Setren", "Sidokare", "Sukorejo", "Talun", "Tritik"],
            "Sawahan" => ["Sawahan", "Bareng", "Bendolo", "Duren", "Glagahan", "Kebonagung", "Margopatut", "Ngliman", "Pudakit", "Sidorejo"],
            "Sukomoro" => ["Sukomoro", "Bagor", "Blitaran", "Bungur", "Kedungdowo", "Kuwulu", "Nglundo", "Ngumpul", "Ngrami", "Pehserut", "Putren", "Sumengko"],
            "Tanjunganom" => ["Tanjunganom", "Warujayeng", "Banjaranyar", "Demangan", "Getas", "Jogomerto", "Kedungrejo", "Kedungotok", "Malangsari", "Ngadirejo", "Sidoharjo", "Sumberkepuh", "Wates"],
            "Wilangan" => ["Wilangan", "Mancon", "Ngudikan", "Sudimoroharjo", "Sukoharjo", "Teguhan"]
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