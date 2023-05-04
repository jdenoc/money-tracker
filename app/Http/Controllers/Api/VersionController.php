<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response as HttpStatus;

class VersionController extends Controller {

    const CONFIG_VERSION = "app.version";

    public function get() {
        $version = config(self::CONFIG_VERSION);
        $http_code = empty($version) ? HttpStatus::HTTP_NO_CONTENT : HttpStatus::HTTP_OK;
        return response($version, $http_code);
    }

}
