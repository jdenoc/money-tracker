<?php

namespace App\Providers;

use App\Helpers\LogWriter;
use Illuminate\Log\Logger;
use Monolog\Logger as Monolog;
use Illuminate\Log\LogServiceProvider as BaseLogServiceProvider;

class LogServiceProvider extends BaseLogServiceProvider {

    /**
     * Create the logger.
     *
     * @return Logger
     */
    public function createLogger()
    {
        $log = new LogWriter(
            new Monolog($this->channel()), $this->app['events']
        );

        if ($this->app->hasMonologConfigurator()) {
            call_user_func($this->app->getMonologConfigurator(), $log->getMonolog());
        } else {
            $this->configureHandler($log);
        }

        return $log;
    }

    protected function getLogFilePath($filename_suffix = ''){
        $filename_suffix = empty($filename_suffix) ? '' : '.'.$filename_suffix;
        return storage_path('logs/laravel'.$filename_suffix.'.log');
    }

    /**
     * Configure the Monolog handlers for the application.
     *
     * @param  Logger  $log
     *      $log is actually of type App\Helpers\LogWriter.
     *      PHP Freaks out if I change a previously defined variable type for a method of an extended class.
     *      App\Helpers\LogWriter extends Illuminate\Log\Writer so they're more or less the same thing anyway,
     *      only with a few changes :P
     * @return void
     */
    protected function configureSingleHandler(Logger $log){
        $can_write_log = false;
        foreach($log->getLogLevels() as $log_level){    // getLogLevels() comes from App\Helpers\LogWriter
            if($can_write_log || $log_level == $this->logLevel()){
                $can_write_log = true;
            }

            if($can_write_log){
                $log->useFiles($this->getLogFilePath($log_level), $log_level);
            }
        }
    }

    /**
     * Configure the Monolog handlers for the application.
     *
     * @param  Logger  $log
     *      $log is actually of type App\Helpers\LogWriter.
     *      PHP Freaks out if I change a previously defined variable type for a method of an extended class.
     *      App\Helpers\LogWriter extends Illuminate\Log\Writer so they're more or less the same thing anyway,
     *      only with a few changes :P
     * @return void
     */
    protected function configureDailyHandler(Logger $log){
        $can_write_log = false;
        foreach($log->getLogLevels() as $log_level){    // getLogLevels() comes from App\Helpers\LogWriter
            if($can_write_log || $log_level == $this->logLevel()){
                $can_write_log = true;
            }

            if($can_write_log){
                $log->useDailyFiles($this->getLogFilePath($log_level), $this->maxFiles(), $log_level);
            }
        }
    }

}