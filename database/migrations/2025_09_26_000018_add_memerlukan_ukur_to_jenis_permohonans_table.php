<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('jenis_permohonans', function (Blueprint $table) {
            // Menambahkan kolom baru untuk menandai apakah jenis permohonan memerlukan pengukuran.
            // Defaultnya adalah false (tidak perlu).
            $table->boolean('memerlukan_ukur')->default(false)->after('waktu_timeline_hari');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jenis_permohonans', function (Blueprint $table) {
            $table->dropColumn('memerlukan_ukur');
        });
    }
};
