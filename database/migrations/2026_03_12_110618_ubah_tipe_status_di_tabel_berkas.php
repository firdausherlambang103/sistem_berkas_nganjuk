<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Mengubah tipe kolom status dari ENUM menjadi VARCHAR (String)
        DB::statement("ALTER TABLE berkas MODIFY COLUMN status VARCHAR(255) DEFAULT 'Diproses'");
    }

    public function down(): void
    {
        // Jika di-rollback, kembalikan menjadi ENUM
        DB::statement("ALTER TABLE berkas MODIFY COLUMN status ENUM('Diproses', 'Selesai', 'Ditutup', 'Pending') DEFAULT 'Diproses'");
    }
};