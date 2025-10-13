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
        Schema::table('tims', function (Blueprint $table) {
            $table->string('nomor_sk')->nullable()->after('nama_tim');
            $table->date('tanggal_sk')->nullable()->after('nomor_sk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tims', function (Blueprint $table) {
            $table->dropColumn(['nomor_sk', 'tanggal_sk']);
        });
    }
};
