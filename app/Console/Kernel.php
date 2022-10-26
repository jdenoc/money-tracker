<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel {

    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\AccountTotalSanityCheck::class,
        Commands\ClearTmpUploads::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     */
    protected function schedule(Schedule $schedule){
        // make sure entry totals add up to account totals
        $schedule->command("sanity-check:account-total")->dailyAt("03:17");
        // clear contents of storage/app/tmp-uploads/
        $schedule->command('storage:clear-tmp-uploads')->daily();
        // database backup
        $schedule->command('snapshot:create '.date('Ymd.His.e').' --compress')->dailyAt("02:15");
        // clear stale backups
        $schedule->command('snapshot:cleanup --keep=30')->dailyAt("02:45");
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(){
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }

}