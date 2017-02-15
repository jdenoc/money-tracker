<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Http\Response;

abstract class TestCase extends BaseTestCase {

    use CreatesApplication;

     /**
     * @param Response $response
     * @return array|null
     */
    protected function getResponseAsArray($response){
        $response_body = $response->getContent();
        return json_decode($response_body, true);
    }

}
