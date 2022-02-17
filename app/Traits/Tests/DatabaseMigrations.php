<?php

namespace App\Traits\Tests;

use Spatie\DbDumper\Databases\MySql;

trait DatabaseMigrations {

    protected static $DB_DUMP_FILE = '';
    protected static $FRESH_RUN = true;

    public function migrate(){
        if(self::$FRESH_RUN){
            // load database migrations and seed with data
            $this->artisan('migrate:fresh', ['--seeder'=>'UiSampleDatabaseSeeder']);

            // dump database contents to file
            $database_connection_config_prefix = 'database.connections.mysql.';
            self::$DB_DUMP_FILE = __DIR__.'/Browser/db-dump/'.__CLASS__.'.sql';
            MySql::create()
                ->setDbName(config($database_connection_config_prefix.'database'))
                ->setHost(config($database_connection_config_prefix.'host'))
                ->setUserName(config($database_connection_config_prefix.'username'))
                ->setPassword(config($database_connection_config_prefix.'password'))
                ->dumpToFile(self::$DB_DUMP_FILE);
        } else {
            // init database from file
            $this->artisan('migrate:fresh-from-file', ['file'=>self::$DB_DUMP_FILE]);
        }
    }

    public static function cleanup(){
        if(file_exists(self::$DB_DUMP_FILE)){
            unlink(self::$DB_DUMP_FILE);
        }
        self::$DB_DUMP_FILE = '';
        self::$FRESH_RUN = true;
    }

}
