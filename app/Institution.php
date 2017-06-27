<?php

namespace App;

class Institution extends BaseModel {

    protected $table = 'institutions';
    public $timestamps = false; // turns off default laravel time stamping
    protected $fillable = [
        'name'
    ];
    protected $guarded = [
        'id'
    ];
    protected $casts = [
        'active'=>'boolean'
    ];

    public function accounts(){
        return $this->hasMany('App\Account', 'institution_id');
    }

}