<?php

namespace Tests;

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
        return $app;
    }

    public function initialiseApplication(){
        $this->app = $this->createApplication();
    }

    public function refreshApplication(){
        if(!$this->app){
            $this->initialiseApplication();
        }
        print_r( 'APP_ENV:'.env('APP_ENV') );  // TODO: remove
        print_r( "\n".'app.env:'.config('app.env') );   // TODO: remove
        print_r( "\n".'DB_DATABASE:'.env('DB_DATABASE') );  // TODO: remove
        print "\n".'database.connections.mysql:';   // TODO: remove
        print_r( config('database.connections.mysql') );    // TODO: remove
    }
}
