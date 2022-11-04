<?php

namespace App\Checks;

use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

class AppKeyCheck extends Check {

    public function run(): Result {
        $result = Result::make();

        if ($this->isAppKeySet()) {
            return $result->ok();
        } else {
            return $result->failed("APP_KEY not set");
        }
    }

    protected function isAppKeySet(): bool {
        return config('app.key') != "";
    }

}
