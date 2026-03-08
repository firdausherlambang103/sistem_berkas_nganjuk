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
        // Jika tabel belum ada, kita buatkan sekalian
        if (!Schema::hasTable('map_layers')) {
            Schema::create('map_layers', function (Blueprint $table) {
                $table->id();
                $table->string('nama_layer');
                $table->string('tabel_db')->nullable();
                $table->string('warna')->default('#3388ff');
                $table->string('color_hm')->nullable();
                $table->string('color_hgb')->nullable();
                $table->string('color_hp')->nullable();
                $table->string('color_hgu')->nullable();
                $table->string('color_wakaf')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        } else {
            // Jika tabel sudah ada, kita tambahkan kolom warna spesifik hak
            Schema::table('map_layers', function (Blueprint $table) {
                if (!Schema::hasColumn('map_layers', 'color_hm')) {
                    $table->string('color_hm')->nullable()->after('warna');
                }
                if (!Schema::hasColumn('map_layers', 'color_hgb')) {
                    $table->string('color_hgb')->nullable()->after('color_hm');
                }
                if (!Schema::hasColumn('map_layers', 'color_hp')) {
                    $table->string('color_hp')->nullable()->after('color_hgb');
                }
                if (!Schema::hasColumn('map_layers', 'color_hgu')) {
                    $table->string('color_hgu')->nullable()->after('color_hp');
                }
                if (!Schema::hasColumn('map_layers', 'color_wakaf')) {
                    $table->string('color_wakaf')->nullable()->after('color_hgu');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('map_layers')) {
            Schema::table('map_layers', function (Blueprint $table) {
                $table->dropColumn(['color_hm', 'color_hgb', 'color_hp', 'color_hgu', 'color_wakaf']);
            });
        }
    }
};