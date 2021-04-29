<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Account extends BaseModel {

    use HasFactory;

    protected $table = 'accounts';
    public $timestamps = false; // turns off default laravel time stamping
    protected $fillable = [
        'name', 'institution_id' ,'total'
    ];
    protected $guarded = [
        'id'
    ];
    protected $casts = [
        'disabled'=>'boolean',
        'total'=>'float'
    ];
    protected $dates = [
        'create_stamp',
        'modified_stamp',
        'disabled_stamp'
    ];

    private static $required_fields = [
        'name',
        'institution_id',
        'disabled',
        'total',
        'currency',
    ];

    public function institution(){
        $this->belongsTo('App\Modals\Institution', 'institution_id');
    }

    public function account_types(){
        return $this->hasMany('App\Models\AccountType', 'account_id');
    }

    public function update_total($value){
        $this->total += $value;
        $this->save();
    }

    public static function find_account_with_types($account_id){
        $account = Account::with('account_types')->where('id', $account_id);
        return $account->first();
    }

    public static function getRequiredFieldsForUpdate(){
        return self::$required_fields;
    }

    public static function getRequiredFieldsForCreation(){
        $fields = self::$required_fields;
        unset($fields[array_search('disabled', $fields)]);
        // using array_values here to reset the array index
        // after we unset the "disabled" element
        return array_values($fields);
    }

}