<?php

namespace App;

use Illuminate\Support\Facades\Storage;

class Attachment extends BaseModel {

    const STORAGE_TMP_UPLOAD = 'tmp_uploads';
    const STORAGE_ATTACHMENTS = 'attachments';

    protected $table = 'attachments';
    protected $primaryKey = 'uuid';
    public $incrementing = false;   // because attachments.uuid isn't an int and so can be incremented
    protected $fillable = [
        'uuid', 'entry_id', 'attachment'
    ];
    protected $guarded = [
        'stamp'
    ];
    protected $dates = [
        'stamp'
    ];
    public $timestamps = false;

    public function entry(){
        return $this->belongsTo('App\Entry');
    }

    /**
     * Returns attachment filename extension
     * @return string
     */
    public function get_filename_extension(){
        return pathinfo($this->attachment, PATHINFO_EXTENSION);
    }

    /**
     * Return a hashed version of the attachment filename
     * @return string
     */
    public function get_hashed_filename(){
        $ext = $this->get_filename_extension();
        return sha1($this->attachment.$this->uuid).'.'.$ext;
    }

    /**
     * @return string
     */
    public function get_tmp_filename(){
        return $this->uuid.'.'.str_replace(' ', '_', $this->attachment);
    }

    /**
     * @param $file_contents
     * @param bool $is_tmp
     * @return bool
     */
    public function storage_store($file_contents, $is_tmp = false){
        $storage_filename = $is_tmp ? $this->get_tmp_file_path() : $this->get_storage_file_path();
        return Storage::put($storage_filename, $file_contents);
    }

    /**
     * @param bool $is_tmp
     * @return bool
     */
    public function storage_exists($is_tmp = false){
        $storage_filename = $is_tmp ? $this->get_tmp_file_path() : $this->get_storage_file_path();
        return Storage::exists($storage_filename);
    }

    /**
     * @param bool $is_tmp
     * @return bool
     */
    public function storage_delete($is_tmp = false){
        $storage_filename = $is_tmp ? $this->get_tmp_file_path() : $this->get_storage_file_path();
        return Storage::delete($storage_filename);
    }

    public function storage_move_from_tmp_to_main(){
        if($this->storage_exists(true) && !$this->storage_exists()){
            Storage::move($this->get_tmp_file_path(), $this->get_storage_file_path());
        }
    }

    /**
     * @return string
     */
    private function get_tmp_file_path(){
        return self::STORAGE_TMP_UPLOAD.DIRECTORY_SEPARATOR.$this->get_tmp_filename();
    }

    /**
     * @return string
     */
    public function get_storage_file_path(){
        return self::STORAGE_ATTACHMENTS.DIRECTORY_SEPARATOR.$this->get_hashed_filename();
    }

}