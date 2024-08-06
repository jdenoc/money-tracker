<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Spatie\DbSnapshots\Commands\Cleanup as SnapshotCleanup;
use Spatie\DbSnapshots\Commands\Create as SnapshotCreate;
use Spatie\Health\Commands\RunHealthChecksCommand;
use Spatie\Health\Commands\ScheduleCheckHeartbeatCommand;
use Spatie\ScheduleMonitor\Models\MonitoredScheduledTaskLogItem;

class Kernel extends ConsoleKernel {

    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     */
    protected function schedule(Schedule $schedule): void {
        $schedule->command(Commands\AccountTotalSanityCheck::class)->dailyAt("03:17");
        $schedule->command(Commands\ClearTmpUploads::class)->daily();
        $schedule->command(SnapshotCreate::class, [date('Ymd.His.e'), '--compress'])->dailyAt("02:15")
            ->monitorName("snapshot:create {date} --compress"); // specifically used for schedule monitoring
        $schedule->command(SnapshotCleanup::class, ['--keep=30'])->dailyAt("02:45");
        $schedule->command('model:prune', ['--model' => MonitoredScheduledTaskLogItem::class])->dailyAt("01:13")
            ->description("Clear old schedule monitoring logs");
        $schedule->command(RunHealthChecksCommand::class)->everyMinute();
        $schedule->command(ScheduleCheckHeartbeatCommand::class)->everyMinute()
            ->description('Health Check for scheduler');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }

}
