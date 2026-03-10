<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // KITA PAKSA AGAR MENAMBAH KOLOM DI POSTGRESQL
        Schema::connection('pgsql')->table('map_layers', function (Blueprint $table) {
            // Cek dan tambah kolom tipe_layer
            if (!Schema::connection('pgsql')->hasColumn('map_layers', 'tipe_layer')) {
                $table->string('tipe_layer')->default('standar');
            }
            // Tambah kolom untuk fitur Custom Warna Baru
            if (!Schema::connection('pgsql')->hasColumn('map_layers', 'warna_standar')) {
                $table->string('warna_standar')->nullable();
            }
            if (!Schema::connection('pgsql')->hasColumn('map_layers', 'khusus_header')) {
                $table->string('khusus_header')->nullable();
            }
            if (!Schema::connection('pgsql')->hasColumn('map_layers', 'khusus_colors')) {
                $table->json('khusus_colors')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::connection('pgsql')->table('map_layers', function (Blueprint $table) {
            $table->dropColumn(['tipe_layer', 'warna_standar', 'khusus_header', 'khusus_colors']);
        });
    }
};