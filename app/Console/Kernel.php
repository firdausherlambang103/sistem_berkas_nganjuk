<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // PERUBAHAN: Menjalankan command baru setiap menit.
        $schedule->command('berkas:accept-overdue')->everyMinute();

        // Anda bisa tetap menjalankan command lama jika masih diperlukan
        // $schedule->command('berkas:auto-accept')->everyMinute();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
