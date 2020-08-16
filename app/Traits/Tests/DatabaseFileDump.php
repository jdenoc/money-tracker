<?php

namespace App\Traits\Tests;

use Spatie\DbDumper\Databases\MySql;
use Symfony\Component\Finder\Finder;

trait DatabaseFileDump {

    private static $DUMP_DIR = __DIR__.'/../../../tests/Browser/db-dump/';
    private static $DATABASE_CONNECTION_CONFIG_PREFIX = 'database.connections.mysql.';

    public function prepareApplicationForDatabaseDumpFile($test_name){
        $this->purgeDatabaseFileDumps();

        $this->beforeApplicationDestroyed(function() use ($test_name){
            $this->generateDatabaseDumpFile($test_name);
        });
    }

    /**
     * @param string $test_name
     * @throws \Spatie\DbDumper\Exceptions\CannotStartDump
     * @throws \Spatie\DbDumper\Exceptions\DumpFailed
     */
    public function generateDatabaseDumpFile($test_name){
        if(empty($test_name)){
            $test_name = microtime(true);
        }

        MySql::create()
            ->setDbName(config(self::$DATABASE_CONNECTION_CONFIG_PREFIX.'database'))
            ->setHost(config(self::$DATABASE_CONNECTION_CONFIG_PREFIX.'host'))
            ->setUserName(config(self::$DATABASE_CONNECTION_CONFIG_PREFIX.'username'))
            ->setPassword(config(self::$DATABASE_CONNECTION_CONFIG_PREFIX.'password'))
            ->dumpToFile(self::$DUMP_DIR.$test_name.'.sql');
    }

    /**
     * Purge the database dump files
     *
     * Code more or less pulled directly from
     *      vendor/laravel/dusk/src/Console/DuskCommand.php
     *      purgeScreenshots()
     *      purgeConsoleLogs()
     *
     * @return void
     */
    public function purgeDatabaseFileDumps(){
        $files = Finder::create()->files()
            ->in(self::$DUMP_DIR)
            ->name('*.sql');

        foreach ($files as $file) {
            @unlink($file->getRealPath());
        }
    }

}