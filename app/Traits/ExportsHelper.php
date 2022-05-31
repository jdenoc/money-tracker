<?php

namespace App\Traits;

use Storage;

trait ExportsHelper {

    private static $STORAGE_DOWNLOAD_DIR = 'test/downloads/';

    private $absolute_download_dir;

    protected function setAbsoluteDownloadDir(){
        $this->absolute_download_dir = storage_path('app/'.self::$STORAGE_DOWNLOAD_DIR);
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

    protected function pregenerateExportFilenameAtStartOfSecond():string{
        // without this do-while loop we run the risk of generating
        // a filename that is off by 1 seconds from the download
        do{
            $filename = $this->generateExportFilename();
            $microtime = explode(' ', microtime())[0];
        }while($microtime > 0.2 || $microtime < 0.1);
        return $filename;
    }

    protected function getCsvHeaderLine():array{
        return ['ID','Date','Memo','Income','Expense','AccountType','Attachment','Transfer','Tags'];
    }

    protected function clearStorageDownloadDir(){
        foreach(Storage::files(self::$STORAGE_DOWNLOAD_DIR) as $f){
            if(!\Str::contains($f, '.gitignore')){
                Storage::delete($f);
            }
        }
    }

}
