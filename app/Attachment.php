<?php

namespace App;

class Attachment extends BaseModel {

    protected $table = 'attachments';
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