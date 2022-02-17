<?php

namespace Tests;

use App\Traits\Tests\DatabaseFileDump;
use App\Traits\Tests\DatabaseMigrations;

/**
 * Class DuskWithMigrationsTestCase
 *
 * @package Tests
 *
 * This class exists purely so that we can cut down on the amount of code duplication.
 * This way Dusk test classes don't need to include the DatabaseMigrations trait
 * Instead, we can front-load the database schema & data seeding, export it to an SQL file
 * and on subsequent tests loan that file.
 */
abstract class DuskWithMigrationsTestCase extends DuskTestCase {

    /**
     * Often when a test involving a database fails, the failure is very database content dependent.
     * The best way to re-create said failure is to perform a database dump.
     * Doing this will allow us to re-produce the failing issue and in doing so, allow us to fix the issue.
     */
    use DatabaseFileDump;

    use DatabaseMigrations;

    public function setUp(): void{
        parent::setUp();
        $this->migrate();
    }

    public static function tearDownAfterClass(): void{
        self::cleanup();
        parent::tearDownAfterClass();
    }

}
