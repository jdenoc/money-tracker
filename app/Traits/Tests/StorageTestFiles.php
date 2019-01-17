<?php

namespace App\Traits\Tests;

trait StorageTestFiles {

    protected static $storage_location = "app/test";

    protected static $test_file_paths = [
        "app/test/nature-thunderstorm.jpg",
        "app/test/space-blackhole-with-jet.jpg",
        "app/test/test-pattern.png",
        "app/test/test-triangle.png",
        "app/test/crab-nebula.gif",
        "app/test/gravitational-wave.gif",
        "app/test/ipsum-lorem.txt",
        "app/test/lorem-ipsum.txt",
        "app/test/ipsum-lorem.pdf",
        "app/test/lorem-ipsum.pdf",
    ];

    /**
     * @param int $storage_filepath_index
     * @return string
     */
    public function getTestFileStoragePathFromIndex($storage_filepath_index){
        if(!in_array($storage_filepath_index, array_keys(self::$test_file_paths))){
            $test_file_count = count(self::$test_file_paths);
            throw new \OutOfBoundsException("File number provided does not exist. There are ".$test_file_count.", numbered 0-".($test_file_count-1));
        }
        return self::$test_file_paths[$storage_filepath_index];
    }

    /**
     * @param string $filename
     * @return string
     */
    public function getTestFileStoragePathFromFilename($filename){
        $storage_filepath_key = array_search(self::$storage_location.'/'.$filename, self::$test_file_paths);
        if($storage_filepath_key === false){
            throw new \OutOfBoundsException("Filename provided does not match any ");
        }
        return $this->getTestFileStoragePathFromIndex($storage_filepath_key);
    }

    /**
     * @return string
     */
    public function getRandomTestFileStoragePath(){
        return self::$test_file_paths[array_rand(self::$test_file_paths, 1)];
    }

}