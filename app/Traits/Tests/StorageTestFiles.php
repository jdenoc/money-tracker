<?php

namespace App\Traits\Tests;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait StorageTestFiles {

    protected static string $storage_test_attachment_path = "attachments/";   // storage/tests/attachments
    public static string $TEST_STORAGE_DISK_NAME = 'tests';

    public static function getTestStorageAttachmentFilePaths(): array{
        $file_paths = Storage::disk('tests')->files(self::$storage_test_attachment_path);
        $file_paths = array_filter($file_paths, function(string $filename){
            return !Str::contains($filename, ['git', 'DS_Store']);
        });
        return array_values($file_paths);
    }

    public static function getTestStorageAttachmentFilenames(): array{
        $file_paths = self::getTestStorageAttachmentFilePaths();
        return array_map(function(string $file_path){
            return str_replace(self::$storage_test_attachment_path, '', $file_path);
        }, $file_paths);
    }

    public function getTestStorageFileAttachmentFilePathFromFilename(string $filename): string{
        if(!in_array(self::$storage_test_attachment_path.$filename, self::getTestStorageAttachmentFilePaths())){
            throw new \OutOfBoundsException("filename [$filename] does not exist within ".Storage::disk(self::$TEST_STORAGE_DISK_NAME)->path(self::$storage_test_attachment_path));
        }
        return self::$storage_test_attachment_path.$filename;
    }

    public function getFullPathOfRandomAttachmentFromTestStorage(): string{
        $file_paths = self::getTestStorageAttachmentFilePaths();
        return Storage::disk(self::$TEST_STORAGE_DISK_NAME)->path($file_paths[array_rand($file_paths, 1)]);
    }

    public function copyFromTestDiskToAppDisk($testDiskFilePath, $appDiskFilePath){
        Storage::disk('app')->put($appDiskFilePath, Storage::disk(self::$TEST_STORAGE_DISK_NAME)->get($testDiskFilePath));
    }

}