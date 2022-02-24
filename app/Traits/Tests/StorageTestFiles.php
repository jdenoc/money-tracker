<?php

namespace App\Traits\Tests;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait StorageTestFiles {

    protected static string $storage_path = "test/";   // storage/app/test/

    public static function getTestFilePaths(): array{
        $file_paths =  Storage::files(self::$storage_path);
        $file_paths = array_filter($file_paths, function(string $filename){
            return !Str::contains($filename, ['git', 'DS_Store']);
        });
        return array_values($file_paths);
    }

    public static function getTestFilenames(): array{
        $file_paths = self::getTestFilePaths();
        return array_map(function(string $file_path){
            return str_replace(self::$storage_path, '', $file_path);
        }, $file_paths);
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
        $file_paths = self::getTestFilePaths();
        return $file_paths[array_rand($file_paths, 1)];
    }

}