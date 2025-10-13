    <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration
    {
        public function up(): void
        {
            Schema::create('area_kerja_petugas', function (Blueprint $table) {
                $table->id();
                // Menghubungkan ke user yang menjadi petugas ukur
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                // Menghubungkan ke kecamatan yang menjadi area kerjanya
                $table->foreignId('kecamatan_id')->constrained('kecamatans')->onDelete('cascade');
                // Tidak perlu timestamps untuk tabel pivot sederhana
            });
        }

        public function down(): void
        {
            Schema::dropIfExists('area_kerja_petugas');
        }
    };