<?php

namespace App\Traits\Tests;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait StorageTestFiles {

    protected static string $storage_test_attachment_path = "attachments/";   // storage/tests/attachments

    public static function getTestFilePaths(): array{
        $file_paths = Storage::disk('tests')->files(self::$storage_test_attachment_path);
        $file_paths = array_filter($file_paths, function(string $filename){
            return !Str::contains($filename, ['git', 'DS_Store']);
        });
        return array_values($file_paths);
    }

    public static function getTestFilenames(): array{
        $file_paths = self::getTestFilePaths();
        return array_map(function(string $file_path){
            return str_replace(self::$storage_test_attachment_path, '', $file_path);
        }, $file_paths);
    }

    public function getTestFileStoragePathFromFilename(string $filename): string{
        if(!in_array(self::$storage_test_attachment_path.$filename, self::getTestFilePaths())){
            throw new \OutOfBoundsException("filename [$filename] does not exist and so can not provide a file path");
        }
        return self::$storage_test_attachment_path.$filename;
    }

    public function getRandomTestFileStoragePath(): string{
        $file_paths = self::getTestFilePaths();
        return $file_paths[array_rand($file_paths, 1)];
    }

}