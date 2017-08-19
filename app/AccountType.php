<?php

namespace App;

class AccountType extends BaseModel {

    protected $table = 'account_types';
    protected $fillable = [
        'type', 'last_digits', 'type_name', 'account_id', 'disabled'
    ];
    protected $guarded = [
        'id', 'modified_stamp'
    ];
    protected $casts = [
        'disabled'=>'boolean'
    ];
    protected $dates = [
        'create_stamp', 'modified_stamp', 'disabled_stamp'
    ];
    public $timestamps = false; // turns off default laravel time stamping

    public function account(){
        return $this->belongsTo('App\Account', 'account_id');
    }

    public function entries(){
        return $this->hasMany('App\Entry', 'account_type');
    }

}