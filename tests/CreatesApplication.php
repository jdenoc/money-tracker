<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

trait CreatesApplication {

    /**
     * Creates the application.
     */
    public function createApplication(): Application {
        $app = require __DIR__.'/../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();
        return $app;
    }

    public function initialiseApplication() {
        $this->app = $this->createApplication();
    }

    public function refreshApplication() {
        if (!$this->app) {
            $this->initialiseApplication();
        }
    }

}
