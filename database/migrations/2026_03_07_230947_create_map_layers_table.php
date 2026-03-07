<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Paksa tabel ini dibuat di PostgreSQL, bukan di MySQL
        Schema::connection('pgsql')->create('map_layers', function (Blueprint $table) {
            $table->id();
            $table->string('nama_layer');
            $table->string('tabel_db')->unique(); 
            $table->string('warna')->default('#3388ff');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::connection('pgsql')->dropIfExists('map_layers');
    }
};
