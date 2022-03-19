<?php

namespace App\Models;

class Tag extends BaseModel {

    protected $table = 'tags';
    public $timestamps = false; // turns off default laravel timestamping
    protected $fillable = [
        'name'
    ];
    protected $guarded = [
        'id'
    ];

    public function entries(){
        return $this->belongsToMany('App\Models\Entry', 'entry_tags', 'tag_id', 'entry_id');
    }

}