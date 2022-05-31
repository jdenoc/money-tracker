<?php

namespace App\Traits;

trait ExportsHelper {

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

}
