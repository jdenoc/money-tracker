<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class QueryLoggingServiceProvider extends ServiceProvider {

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(){
        if(config("app.debug", false)){
            // log queries.
            // solution found here: https://scotch.io/tutorials/debugging-queries-in-laravel#toc-listening-for-query-events
            DB::listen(function($query){
                $log_message = str_replace('?', '%s', $query->sql);
                $log_message = vsprintf($log_message, $query->bindings).';';
                $log_message .= " Time:".$query->time.' milliseconds';
                Log::info($log_message);
            });
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register(){
        //
    }

}