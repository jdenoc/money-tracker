<?php

namespace App\Checks;

use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

class AppVersionCheck extends Check {

    public function run(): Result {
        $result = Result::make();

        if($this->isAppVersionSet()){
            return $result->ok();
        } else {
            return $result->failed("APP_VERSION not set");
        }
    }

    protected function isAppVersionSet():bool {
        return config('app.version') != "";
    }

}