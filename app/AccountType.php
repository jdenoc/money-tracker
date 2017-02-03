<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AccountType extends Model {

    protected $table = 'account_types';
    public $timestamps = false; // turns off default laravel time stamping
    protected $fillable = [
        'type', 'last_digits', 'type_name', 'account_group', 'disabled'
    ];
    protected $guarded = [
        'id','last_updated'
    ];

    public function account(){
        return $this->belongsTo('App\Account', 'account_group');
    }

    public function entries(){
        return $this->hasMany('App\Entry', 'account_type');
    }

}