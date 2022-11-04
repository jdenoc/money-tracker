<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class VersionController extends Controller {

    const CONFIG_VERSION = "app.version";

    public function get() {
        return response(config(self::CONFIG_VERSION));
    }

}
