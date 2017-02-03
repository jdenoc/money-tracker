<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Account extends Model {

    protected $table = 'accounts';
    public $timestamps = false; // turns off default laravel time stamping
    protected $fillable = [
        'account', 'total'
    ];
    protected $guarded = [
        'id'
    ];

    public function account_types(){
        return $this->hasMany('App\AccountType', 'account_group');
    }

}