<?php

namespace App\Providers\Faker;

use App\Traits\Tests\StorageTestFiles;
use Faker\Provider\Base as FakerProviderBase;

class ProjectFilenameProvider extends FakerProviderBase {

    use StorageTestFiles;

    public static function filename(){
        return basename(static::randomElement(static::$test_file_paths));
    }

}