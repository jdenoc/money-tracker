<?php

namespace App\Traits\Tests;

trait HealthCheck {

    // look at the HealthServiceProvider for a full list
    private array $health_info_labels = [
        "App Key",
        "App Version",
        "Cache:file",
        "Cache:memcached",
        "Database",
        "Database Connection Count",
        "Database Migrations",
        "Debug Mode",
        "Environment",
        "Optimized App",
        "Schedule",
        "Used Disk Space",
    ];

}
