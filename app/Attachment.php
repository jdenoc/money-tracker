<?php

namespace App;

class Attachment extends BaseModel {

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

}