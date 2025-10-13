    <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration
    {
        public function up(): void
        {
            Schema::create('petugas_ukur', function (Blueprint $table) {
                $table->id();
                // Menghubungkan langsung ke tabel users
                $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
                $table->string('keahlian')->nullable()->comment('Contoh: Pengukuran, Pemetaan, dll');
                $table->timestamps();
            });
        }

        public function down(): void
        {
            Schema::dropIfExists('petugas_ukur');
        }
    };
    

