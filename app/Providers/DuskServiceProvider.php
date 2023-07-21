<?php

namespace App\Providers;

use Laravel\Dusk\DuskServiceProvider as DuskServiceProviderBase;

class DuskServiceProvider extends DuskServiceProviderBase {

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot() {
        parent::boot(); // needed to allow original DuskServiceProvider to do its thing
    }

    /**
     * Register the application services.
     *
     * @return void
     * @throws \Exception
     */
    public function register() {
        parent::register();  // needed to allow original DuskServiceProvider to do its thing
    }

}
