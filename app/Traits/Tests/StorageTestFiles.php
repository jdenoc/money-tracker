<?php

namespace App\Traits\Tests;

use Illuminate\Support\Facades\Storage;

trait StorageTestFiles {

    protected static $storage_path = "test/";

    /**
     * @return array
     */
    public static function getTestFilePaths(): array{
        $file_paths =  Storage::files(self::$storage_path);
        $file_paths = array_filter($file_paths, function(string $filename){
            if(strpos($filename, 'git') === false){
                return $filename;
            }
        });
        return array_values($file_paths);
    }

    /**
     * @return array
     */
    public static function getTestFilenames(): array{
        $file_paths = self::getTestFilePaths();
        $filenames = array_map(function(string $file_path){
            return str_replace(self::$storage_path, '', $file_path);
        }, $file_paths);
        return $filenames;
    }

    /**
     * @param string $filename
     * @return string
     */
    public function getTestFileStoragePathFromFilename(string $filename): string{
        $storage_filepath_key = array_search(self::$storage_path.$filename, self::getTestFilePaths());
        if($storage_filepath_key === false){
            throw new \OutOfBoundsException("Filename provided does not match any ");
        }
        return self::$storage_path.$filename;
    }

    /**
     * @return string
     */
    public function getRandomTestFileStoragePath(): string{
        $filenames = self::getTestFilenames();
        return self::$storage_path.$filenames[array_rand($filenames, 1)];
    }

}