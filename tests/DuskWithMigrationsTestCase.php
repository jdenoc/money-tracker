<?php

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * Class DuskWithMigrationsTestCase
 *
 * @package Tests
 *
 * This class exists purely so that we can cut down on the amount of code duplication.
 * This way Dusk test classes don't need to include the database refresh trait
 */
abstract class DuskWithMigrationsTestCase extends DuskTestCase {

    /**
     * This trait is not used in the DuskTestCase class for those instances
     * when a database migration is not required for said tests.
     */
    use DatabaseMigrations;

    public function setUp(){
        parent::setUp();
        $this->seed('UiSampleDatabaseSeeder');  // run database seeder
    }

}
