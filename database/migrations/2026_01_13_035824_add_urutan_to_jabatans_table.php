<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
{
    Schema::table('jabatans', function (Blueprint $table) {
        // Default 99 agar jabatan baru otomatis ditaruh di bawah jika tidak diisi
        $table->integer('urutan')->default(99)->after('nama_jabatan'); 
    });
}

public function down(): void
{
    Schema::table('jabatans', function (Blueprint $table) {
        $table->dropColumn('urutan');
    });
}
};
