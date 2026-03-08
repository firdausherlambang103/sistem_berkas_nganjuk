<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Memaksa tabel ini dibuat KHUSUS di dalam PostgreSQL
        Schema::connection('pgsql')->create('spatial_features', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->json('properties')->nullable();
            
            // Cukup simpan ID-nya saja, hapus constraint foreign key ke MySQL
            $table->unsignedBigInteger('layer_id')->nullable();
            
            $table->timestamps();
        });

        // Memaksa fungsi PostGIS dieksekusi di database PostgreSQL
        DB::connection('pgsql')->statement('ALTER TABLE spatial_features ADD COLUMN geom geometry(Geometry, 4326)');
        DB::connection('pgsql')->statement('CREATE INDEX spatial_features_geom_idx ON spatial_features USING GIST (geom)');
    }

    public function down(): void
    {
        Schema::connection('pgsql')->dropIfExists('spatial_features');
    }
};