<?php

namespace Tests\Feature\Api\Get;

use App\Http\Controllers\Api\VersionController;
use Tests\TestCase;
use Faker\Factory as FakerFactory;
use Symfony\Component\HttpFoundation\Response as HttpStatus;

class VersionControllerTest extends TestCase {

    private $_base_uri = '/api/version';

    public function testGetVersion(){
        $faker = FakerFactory::create();
        // GIVEN
        $test_version = $faker->randomDigitNotNull.'.'.$faker->randomDigitNotNull.'.'.$faker->randomDigitNotNull.'-test-'.$faker->word;
        config([VersionController::CONFIG_VERSION=>$test_version]);

        // WHEN
        $get_response = $this->get($this->_base_uri);

        // THEN
        $this->assertResponseStatus($get_response, HttpStatus::HTTP_OK);
        $this->assertEquals($test_version, $get_response->getContent());
    }
}
