<?php

namespace Tests\Feature\Api\Get;

use App\Http\Controllers\Api\VersionController;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Symfony\Component\HttpFoundation\Response as HttpStatus;

class VersionControllerTest extends TestCase {
    use WithFaker;

    private string $_base_uri = '/api/version';

    /**
     * override the RefreshDatabase trait method to prevent the use of said trait in THIS test suite
     */
    public function refreshTestDatabase(): void {
    }

    public function testGetVersion() {
        // GIVEN
        $test_version = $this->faker->randomDigit().'.'.$this->faker->randomDigit().'.'.$this->faker->randomDigit().'-test-'.substr($this->faker->sha1(), 0, 7);
        config([VersionController::CONFIG_VERSION=>$test_version]);

        // WHEN
        $get_response = $this->get($this->_base_uri);

        // THEN
        $this->assertResponseStatus($get_response, HttpStatus::HTTP_OK);
        $this->assertEquals($test_version, $get_response->getContent());
    }

    public function testGetVersionButItHasNotBeenSet() {
        // GIVEN - version should NOT be set by default

        // WHEN
        $get_response = $this->get($this->_base_uri);

        // THEN
        $this->assertResponseStatus($get_response, HttpStatus::HTTP_NO_CONTENT);
        $this->assertEmpty($get_response->getContent());
    }

}
