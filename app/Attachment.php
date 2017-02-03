<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model {

    protected $table = 'attachments';
    public $timestamps = false; // turns off default laravel time stamping
    protected $fillable = [
        'entry_id', 'attachment', 'uid'
    ];
    protected $guarded = [
        'id', 'stamp'
    ];

    public function entry(){
        return $this->belongsTo('App\Entry');
    }

}