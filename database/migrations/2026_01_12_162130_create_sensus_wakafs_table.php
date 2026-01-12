<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sensus_wakafs', function (Blueprint $table) {
            $table->id();
            $table->string('rec_time')->nullable();
            $table->decimal('latitude', 11, 8)->nullable(); // gen_LATI
            $table->decimal('longitude', 11, 8)->nullable(); // gen_LONG
            $table->string('penggunaan')->nullable(); // Masjid/Musholla
            $table->string('pengenal')->nullable(); // Nama Wakaf
            $table->string('status_tanah')->nullable(); // Status_T_1 (SHM, dll)
            $table->string('afiliasi')->nullable(); // Affilias_1 (NU, dll)
            $table->string('kecamatan')->nullable();
            $table->string('desa')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sensus_wakafs');
    }
};