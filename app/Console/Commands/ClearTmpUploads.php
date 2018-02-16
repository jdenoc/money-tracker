<?php

namespace App\Console\Commands;

use App\Attachment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ClearTmpUploads extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:clear-tmp-uploads';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clears the files from the storage/app/tmp_uploads directory';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(){
        $tmp_upload_file_count = 0;
        $tmp_upload_files = Storage::files(Attachment::STORAGE_TMP_UPLOAD);
        foreach($tmp_upload_files as $tmp_upload_file){
            if(strpos($tmp_upload_file, 'gitignore') !== false){
                continue;   // don't delete .gitignore file
            }
            $deleted = Storage::delete($tmp_upload_file);
            if($deleted){
                $tmp_upload_file_count++;
            }
        }

        $this->info($tmp_upload_file_count." files deleted from ".Attachment::STORAGE_TMP_UPLOAD);
    }

}