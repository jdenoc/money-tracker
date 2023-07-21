<?php

namespace Tests;

use App\Traits\Tests\DatabaseMigrations;
use Illuminate\Support\Facades\Cache;

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
    use DatabaseMigrations;

    public function setUp(): void {
        parent::setUp();
        $this->migrate();
    }

    protected function tearDown(): void {
        Cache::flush();
        parent::tearDown();
    }

    public static function tearDownAfterClass(): void {
        self::cleanup();
        parent::tearDownAfterClass();
    }

}
