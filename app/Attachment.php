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

}