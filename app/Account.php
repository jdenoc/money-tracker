<?php

namespace App;

class Account extends BaseModel {

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

    public static function find_account_with_types($account_id){
        $account = Account::with(['account_types'=>function($query){
            $query->where('disabled', 0);
        }])
            ->where('id', $account_id);
        return $account->first();
    }

}