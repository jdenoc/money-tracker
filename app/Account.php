<?php

namespace App;

class Account extends BaseModel {

    protected $table = 'accounts';
    public $timestamps = false; // turns off default laravel time stamping
    protected $fillable = [
        'name', 'institution_id' ,'total'
    ];
    protected $guarded = [
        'id'
    ];
    protected $casts = [
        'disabled'=>'boolean'
    ];
    protected $dates = [
        'create_stamp',
        'modified_stamp',
        'disabled_stamp'
    ];

    public function institution(){
        $this->belongsTo('App\Institution', 'institution_id');
    }

    public function account_types(){
        return $this->hasMany('App\AccountType', 'account_id');
    }

    public function update_total($value){
        $this->total += $value;
        $this->save();
    }

    public static function find_account_with_types($account_id){
        $account = Account::with('account_types')->where('id', $account_id);
        return $account->first();
    }

}