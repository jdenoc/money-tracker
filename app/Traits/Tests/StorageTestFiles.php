<?php

namespace App\Traits\Tests;

trait StorageTestFiles {

    protected static $storage_path = "test/";

    /**
     * @return array
     */
    public static function getTestFilePaths(){
        $file_paths =  \Storage::files(self::$storage_path);
        $file_paths = array_filter($file_paths, function($file_name){
            if(strpos($file_name, 'git') === false){
                return $file_name;
            }
        });
        return array_values($file_paths);
    }

    /**
     * @return array
     */
    public static function getTestFilenames(){
        $file_paths = self::getTestFilePaths();
        $file_names = array_map(function($file_path){
            return str_replace(self::$storage_path, '', $file_path);
        }, $file_paths);
        return $file_names;
    }

    /**
     * @param string $filename
     * @return string
     */
    public function getTestFileStoragePathFromFilename($filename){
        $storage_filepath_key = array_search(self::$storage_path.$filename, self::getTestFilePaths());
        if($storage_filepath_key === false){
            throw new \OutOfBoundsException("Filename provided does not match any ");
        }
        return self::$storage_path.$filename;
    }

    /**
     * @return string
     */
    public function getRandomTestFileStoragePath(){
        $file_names = self::getTestFilenames();
        return self::$storage_path.$file_names[array_rand($file_names, 1)];
    }

}