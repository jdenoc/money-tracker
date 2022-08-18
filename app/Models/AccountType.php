<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class AccountType extends BaseModel {

    use HasFactory;

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

    private static $required_fields = [
        'name',
        'account_id',
        'disabled',
        'type',
        'last_digits',
    ];

    public function account(){
        return $this->belongsTo('App\Models\Account', 'account_id');
    }

    public function entries(){
        return $this->hasMany('App\Models\Entry', 'account_type_id');
    }

    public function save(array $options = []){
        if(!$this->getOriginal('disabled') && $this->disabled){
            $this->disabled_stamp = new Carbon();
        }
        return parent::save($options);
    }

    public static function getEnumValues(){
        return parent::get_enum_values('type');
    }

    public static function getRequiredFieldsForUpdate(){
        return self::$required_fields;
    }

    public static function getRequiredFieldsForCreation(){
        return self::$required_fields;
    }

}