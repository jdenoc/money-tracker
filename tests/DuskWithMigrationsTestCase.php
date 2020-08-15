<?php

namespace Tests;

use App\Traits\Tests\DatabaseFileDump;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * Class DuskWithMigrationsTestCase
 *
 * @package Tests
 *
 * This class exists purely so that we can cut down on the amount of code duplication.
 * This way Dusk test classes don't need to include the DatabaseMigrations trait
 */
abstract class DuskWithMigrationsTestCase extends DuskTestCase {

    /**
     * This trait is not used in the DuskTestCase class for those instances
     * when a database migration is not required for said tests.
     */
    use DatabaseMigrations {
        runDatabaseMigrations as defaultRunDatabaseMigrations;
    }

    /**
     * Often when a test involving a database fails, the failure is very database content dependent.
     * The best way to re-create said failure is to perform a database dump.
     * Doing this will allow us to re-produce the failing issue and in doing so, allow us to fix the issue.
     */
    use DatabaseFileDump;

    public function setUp(): void{
        parent::setUp();
        $this->seed('UiSampleDatabaseSeeder');  // run database seeder
    }

    /**
     * Overriding the method from the DatabaseMigrations trait
     */
    public function runDatabaseMigrations(){
        $this->beforeApplicationDestroyed(static function () {
            // Truncate all tables, except migrations
            // This will speed up tests
            // This is supposed to occur before the migrate:rollback command
            $tables = \DB::select('SHOW TABLES');

            $db_name = config('database.connections.mysql.database');
            $prop = "Tables_in_%s";
            foreach ($tables as $table) {
                $table_name = $table->{sprintf($prop, $db_name)};
                if ($table_name !== 'migrations')
                    \DB::table($table_name)->truncate();
            }
        });
        $this->defaultRunDatabaseMigrations();
    }

}
