<?php

namespace App\Providers;

use App\Checks\AppKeyCheck;
use App\Checks\AppVersionCheck;
use App\Checks\DatabaseMigrationsCheck;
use Illuminate\Support\ServiceProvider;
use Spatie\Health\Checks\Checks\CacheCheck;
use Spatie\Health\Checks\Checks\DatabaseCheck;
use Spatie\Health\Checks\Checks\DatabaseConnectionCountCheck;
use Spatie\Health\Checks\Checks\DebugModeCheck;
use Spatie\Health\Checks\Checks\EnvironmentCheck;
use Spatie\Health\Checks\Checks\ScheduleCheck;
use Spatie\Health\Checks\Checks\UsedDiskSpaceCheck;
use Spatie\Health\Facades\Health;

class HealthServiceProvider extends ServiceProvider {

    /**
     * Register services.
     */
    public function register() {
        $health_checks = [
            AppKeyCheck::new(),
            AppVersionCheck::new(),
            CacheCheck::new()
                ->name('Cache:file')
                ->driver('file'),
            DatabaseCheck::new(),
            DatabaseConnectionCountCheck::new()
                ->warnWhenMoreConnectionsThan(30)
                ->failWhenMoreConnectionsThan(50),
            DatabaseMigrationsCheck::new(),
            DebugModeCheck::new(),
            EnvironmentCheck::new(),
            ScheduleCheck::new(),
            UsedDiskSpaceCheck::new()
                ->failWhenUsedSpaceIsAbovePercentage(85),
        ];

        if (config('cache.default') == 'memcached') {
            $health_checks[] = CacheCheck::new()
                ->name('Cache:memcached')
                ->driver('memcached');
        }
        Health::checks($health_checks);
    }

    /**
     * Bootstrap services.
     */
    public function boot() {
        //
    }

}
