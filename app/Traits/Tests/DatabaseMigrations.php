<?php

namespace App\Traits\Tests;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Spatie\DbSnapshots\Events\CreatedSnapshot;

trait DatabaseMigrations {

    protected static bool $DUMP_READY = false;
    protected static bool $FRESH_RUN = true;
    protected static string $SNAPSHOT_NAME = '';

    public function migrate($with_seeder=true){
        if(self::$FRESH_RUN){
            // init a listener to clean up file
            Event::listen(CreatedSnapshot::class, function(CreatedSnapshot $event){
                $snapshot_file_path =  $event->snapshot->disk->getDriver()->getAdapter()->getPathPrefix().'/'.$event->snapshot->fileName;
                $file_contents = file_get_contents($snapshot_file_path);
                $file_contents = str_replace(';;', ';', $file_contents);
                $file_contents = str_replace('DELIMITER ;', "\n", $file_contents);
                $file_contents = str_replace("/*!50017 DEFINER=`jdenoc`@`%`*/", "", $file_contents);
                file_put_contents($snapshot_file_path, $file_contents);
                self::$DUMP_READY = true;
            });

            // load database migrations and seed with data
            $options = ($with_seeder) ? ['--seeder'=>'UiSampleDatabaseSeeder'] : [];
            $this->artisan('migrate:fresh', $options);

            // dump database contents to file
            self::$SNAPSHOT_NAME = class_basename(get_called_class());
            $this->artisan('snapshot:create', ['name'=>self::$SNAPSHOT_NAME]);

            self::$FRESH_RUN = false;
        } else {
            while(!self::$DUMP_READY){
                // check every second until snapshot is ready
                sleep(1);
            }
            // init database from file
            $this->artisan('snapshot:load', ['name'=>self::$SNAPSHOT_NAME]);
        }
    }

    public static function cleanup(){
//        Artisan::call('snapshot:delete', ['name'=>self::$SNAPSHOT_NAME]);     // TODO: figure out how to make this work
        self::$SNAPSHOT_NAME = '';
        self::$FRESH_RUN = true;
        self::$DUMP_READY = false;
    }

}
