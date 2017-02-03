<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model {

    protected $table = 'tags';
    public $timestamps = false; // turns off default laravel timestamping
    protected $fillable = [
        'tag'
    ];
    protected $guarded = [
        'id'
    ];

    public function entries(){
        return $this->belongsToMany('App\Entry', 'entry_tags', 'tag_id', 'entry_id');
    }

}