<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('nama_status');
            $table->boolean('butuh_waktu_hari')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_statuses');
    }
};