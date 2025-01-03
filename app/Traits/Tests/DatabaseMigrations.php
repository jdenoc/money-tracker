<?php

namespace App\Traits\Tests;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Spatie\DbSnapshots\Events\CreatedSnapshot;

trait DatabaseMigrations {

    protected static bool $DUMP_READY = false;
    protected static bool $FRESH_RUN = true;
    protected static string $SNAPSHOT_NAME = '';

    public function migrate(bool $withSeeder = true) {
        if (config('testing.snapshot.use_latest')) {
            $this->migrateWithLatestSnapshot();
        } else {
            if (self::$FRESH_RUN) {
                $this->migrateFresh($withSeeder);
            } else {
                $this->migrateWithSpecificSnapshot();
            }
        }
        Cache::flush();
    }

    private function migrateWithLatestSnapshot() {
        $this->artisan('snapshot:load', ['--latest' => true]);
    }

    private function migrateFresh(bool $withSeeder) {
        // init a listener to clean up file
        Event::listen(CreatedSnapshot::class, function(CreatedSnapshot $event) {
            $snapshot_file_path = $event->snapshot->disk->path($event->snapshot->fileName);
            $file_contents = file_get_contents($snapshot_file_path);
            $file_contents = str_replace(';;', ';', $file_contents);
            $file_contents = str_replace('DELIMITER ;', "\n", $file_contents);
            $db_username = config('database.connections.'.config('database.default').'.username');
            $file_contents = str_replace("/*!50017 DEFINER=`$db_username`@`%`*/", "", $file_contents);
            file_put_contents($snapshot_file_path, $file_contents);
            self::$DUMP_READY = true;
        });

        // load database migrations and seed with data
        $options = ($withSeeder) ? ['--seeder' => 'UiSampleDatabaseSeeder'] : [];
        $this->artisan('migrate:fresh', $options);

        // dump database contents to file
        self::$SNAPSHOT_NAME = class_basename(get_called_class());
        $this->artisan('snapshot:create', ['name' => self::$SNAPSHOT_NAME]);

        self::$FRESH_RUN = false;
    }

    private function migrateWithSpecificSnapshot() {
        while (!self::$DUMP_READY) {
            // check every second until snapshot is ready
            sleep(1);
        }
        // init database from file
        $this->artisan('snapshot:load', ['name' => self::$SNAPSHOT_NAME]);
    }

    public static function cleanup() {
        // Artisan::call('snapshot:delete', ['name'=>self::$SNAPSHOT_NAME]);     // TODO: figure out how to make this work
        self::$SNAPSHOT_NAME = '';
        self::$FRESH_RUN = true;
        self::$DUMP_READY = false;
    }

}
