<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
        {
            Schema::table('wa_logs', function (Blueprint $table) {
                // Cek dulu biar tidak error kalau sudah ada
                if (!Schema::hasColumn('wa_logs', 'berkas_id')) {
                    $table->foreignId('berkas_id')->nullable()->after('error_message');
                }
                if (!Schema::hasColumn('wa_logs', 'user_id')) {
                    $table->foreignId('user_id')->nullable()->after('berkas_id');
                }
                if (!Schema::hasColumn('wa_logs', 'template_id')) {
                    $table->foreignId('template_id')->nullable()->after('user_id');
                }
            });
        }

        public function down()
        {
            Schema::table('wa_logs', function (Blueprint $table) {
                $table->dropColumn(['berkas_id', 'user_id', 'template_id']);
            });
        }
};
