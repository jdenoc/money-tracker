<?php

namespace App;

class AccountType extends BaseModel {

    const CREATED_AT = 'create_stamp';
    const UPDATED_AT = 'modified_stamp';

    protected $table = 'account_types';
    protected $fillable = [
        'type', 'last_digits', 'name', 'account_id', 'disabled'
    ];
    protected $guarded = [
        'id', 'modified_stamp'
    ];
    protected $casts = [
        'disabled'=>'boolean'
    ];
    protected $dates = [
        'disabled_stamp'
    ];

    public function account(){
        return $this->belongsTo('App\Account', 'account_id');
    }

    public function entries(){
        return $this->hasMany('App\Entry', 'account_type_id');
    }

}