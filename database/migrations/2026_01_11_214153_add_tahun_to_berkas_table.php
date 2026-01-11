<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::table('berkas', function (Blueprint $table) {
        // 1. Tambah kolom tahun (default tahun sekarang)
        $table->year('tahun')->default(date('Y'))->after('nomer_berkas');
        
        // 2. Hapus index unique lama (hanya nomer_berkas)
        // Note: Cek nama index di database Anda, biasanya 'berkas_nomer_berkas_unique'
        $table->dropUnique(['nomer_berkas']); 

        // 3. Buat unique index baru (kombinasi nomer_berkas + tahun)
        $table->unique(['nomer_berkas', 'tahun']);
    });
}

public function down()
{
    Schema::table('berkas', function (Blueprint $table) {
        $table->dropUnique(['nomer_berkas', 'tahun']);
        $table->unique('nomer_berkas');
        $table->dropColumn('tahun');
    });
}
};
