<?php

namespace Tests;

use Illuminate\Support\Facades\Hash;
use Illuminate\Contracts\Console\Kernel;

trait CreatesApplication {

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication(){
        $app = require __DIR__.'/../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();
        Hash::setRounds(4);
        return $app;
    }

    public function initialiseApplication(){
        $this->app = $this->createApplication();
    }

    public function refreshApplication(){
        if(!$this->app){
            $this->initialiseApplication();
        }
    }
}
