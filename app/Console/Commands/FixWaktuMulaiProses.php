<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Berkas;

class FixWaktuMulaiProses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'berkas:fix-waktu-mulai';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Memperbaiki data lama dengan mengisi waktu_mulai_proses yang kosong menggunakan tanggal pembuatan berkas (created_at).';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Mulai proses perbaikan data waktu mulai proses...');

        // Cari semua berkas yang statusnya 'Diproses' atau 'Pending'
        // tetapi 'waktu_mulai_proses'-nya masih kosong.
        $berkasToFix = Berkas::whereIn('status', ['Diproses', 'Pending'])
                             ->whereNull('waktu_mulai_proses')
                             ->get();

        if ($berkasToFix->isEmpty()) {
            $this->info('Tidak ada data berkas yang perlu diperbaiki. Semua sudah lengkap.');
            return 0; // Berhenti jika tidak ada yang perlu diperbaiki
        }

        $this->info("Ditemukan " . $berkasToFix->count() . " berkas yang akan diperbaiki.");

        $bar = $this->output->createProgressBar($berkasToFix->count());
        $bar->start();

        foreach ($berkasToFix as $berkas) {
            // Isi 'waktu_mulai_proses' dengan tanggal 'created_at' sebagai perkiraan.
            $berkas->waktu_mulai_proses = $berkas->created_at;
            $berkas->save();
            $bar->advance();
        }

        $bar->finish();
        $this->info("\nPerbaikan data selesai. Semua berkas lama sekarang memiliki waktu mulai proses.");

        return 0;
    }
}