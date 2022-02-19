<?php

namespace App\Traits\Tests;

trait DatabaseMigrations {

    protected static $DB_DUMP_FILE = '';
    protected static $FRESH_RUN = true;

    public function migrate(){
        if(self::$FRESH_RUN){
            // load database migrations and seed with data
            $this->artisan('migrate:fresh', ['--seeder'=>'UiSampleDatabaseSeeder']);

            // dump database contents to file
            self::$DB_DUMP_FILE = __DIR__.'/../../../tests/db-dump/'.class_basename(get_called_class()).'.sql';
            $this->artisan('db:masked-dump', ['output'=>self::$DB_DUMP_FILE]);

            self::$FRESH_RUN = false;
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
