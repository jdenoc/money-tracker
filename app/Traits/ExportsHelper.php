<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;

trait ExportsHelper {

    private static $STORAGE_DOWNLOAD_DIR = 'downloads/';

    private $absolute_download_dir;

    protected function setAbsoluteDownloadDir(){
        $this->absolute_download_dir = Storage::disk(self::$TEST_STORAGE_DISK_NAME)->path(self::$STORAGE_DOWNLOAD_DIR);
    }

    protected function getAbsoluteDownloadDir(){
        if(is_null($this->absolute_download_dir)){
            throw new \InvalidArgumentException("setAbsoluteDownloadDir() was not called");
        }
        return $this->absolute_download_dir;
    }

    protected function generateExportFilename():string{
        return 'entries.'.now()->getTimestamp().'.csv';
    }

    protected function getCsvHeaderLine():array{
        return ['ID','Date','Memo','Income','Expense','AccountType','Attachment','Transfer','Tags'];
    }

    protected function clearStorageDownloadDir(){
        foreach(Storage::disk(self::$TEST_STORAGE_DISK_NAME)->files(self::$STORAGE_DOWNLOAD_DIR) as $f){
            if(!str_contains($f, '.gitignore')){
                Storage::disk(self::$TEST_STORAGE_DISK_NAME)->delete($f);
            }
        }
    }

}
