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
        Commands\AppVersion::class,
        Commands\ClearTmpUploads::class,
        Commands\TravisCi::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule){
        // $schedule->command('inspire')->hourly();
        $schedule->command('storage:clear-tmp-uploads')->daily();
        $schedule->command("sanity-check:account-total")->dailyAt("03:17");
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands(){
        require base_path('routes/console.php');
    }

}