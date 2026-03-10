<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // KITA PAKSA AGAR DIEKSEKUSI DI POSTGRESQL
        Schema::connection('pgsql')->table('map_layers', function (Blueprint $table) {
            
            // 1. Pastikan tipe_layer ada di pgsql
            if (!Schema::connection('pgsql')->hasColumn('map_layers', 'tipe_layer')) {
                $table->string('tipe_layer')->default('Standar');
            }
            
            // 2. Pastikan kolom warna hak ada di pgsql
            if (!Schema::connection('pgsql')->hasColumn('map_layers', 'color_hm')) {
                $table->string('color_hm')->nullable();
            }
            if (!Schema::connection('pgsql')->hasColumn('map_layers', 'color_hgb')) {
                $table->string('color_hgb')->nullable();
            }
            if (!Schema::connection('pgsql')->hasColumn('map_layers', 'color_hp')) {
                $table->string('color_hp')->nullable();
            }
            if (!Schema::connection('pgsql')->hasColumn('map_layers', 'color_hgu')) {
                $table->string('color_hgu')->nullable();
            }
            if (!Schema::connection('pgsql')->hasColumn('map_layers', 'color_wakaf')) {
                $table->string('color_wakaf')->nullable();
            }
            
        });
    }

    public function down()
    {
        Schema::connection('pgsql')->table('map_layers', function (Blueprint $table) {
            $table->dropColumn([
                'tipe_layer', 
                'color_hm', 
                'color_hgb', 
                'color_hp', 
                'color_hgu', 
                'color_wakaf'
            ]);
        });
    }
};