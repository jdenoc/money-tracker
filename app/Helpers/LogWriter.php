<?php

namespace App\Helpers;

use Illuminate\Log\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;

class LogWriter extends Logger {

    /**
     * @var bool
     * We don't want our logs to "bubble" into different files
     */
    protected $bubble = false;

    /**
     * @return array
     */
    public function getLogLevels(){
        return array_keys($this->levels);
    }

    /**
     * Register a file log handler.
     *
     * @param  string  $path
     * @param  string  $level
     *
     * @throws \Exception                If a missing directory is not buildable
     * @throws \InvalidArgumentException If stream is not a resource or string
     *
     * @return void
     */
    public function useFiles($path, $level = 'debug'){
        $this->monolog->pushHandler($handler = new StreamHandler($path, $this->parseLevel($level), $this->bubble));
        $handler->setFormatter($this->getDefaultFormatter());
    }

    /**
     * Register a daily file log handler.
     *
     * @param  string  $path
     * @param  int     $days
     * @param  string  $level
     * @return void
     */
    public function useDailyFiles($path, $days = 0, $level = 'debug')
    {
        $this->monolog->pushHandler(
            $handler = new RotatingFileHandler($path, $days, $this->parseLevel($level), $this->bubble)
        );

        $handler->setFormatter($this->getDefaultFormatter());
    }

}