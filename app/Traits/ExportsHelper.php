<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;

trait ExportsHelper {

    private static string $STORAGE_DOWNLOAD_DIR = 'downloads/';

    protected function getAbsoluteDownloadDir():string{
        return Storage::disk(self::$TEST_STORAGE_DISK_NAME)->path(self::$STORAGE_DOWNLOAD_DIR);
    }

    protected function generateExportFilename():string{
        return 'entries.'.now()->getTimestamp().'.csv';
    }

    protected function getCsvHeaderLine():array{
        return ['ID','Date','Memo','Income','Expense','AccountType','Attachment','Transfer','Tags'];
    }

    protected function clearStorageDownloadDir():void{
        foreach(Storage::disk(self::$TEST_STORAGE_DISK_NAME)->files(self::$STORAGE_DOWNLOAD_DIR) as $f){
            if(!str_contains($f, '.gitignore')){
                Storage::disk(self::$TEST_STORAGE_DISK_NAME)->delete($f);
            }
        }
    }

}
