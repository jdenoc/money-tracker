<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Institution extends BaseModel {

    use HasFactory;

    const CREATED_AT = 'create_stamp';
    const UPDATED_AT = 'modified_stamp';

    protected $table = 'institutions';
    protected $fillable = [
        'name'
    ];
    protected $guarded = [
        'id'
    ];
    protected $casts = [
        'active'=>'boolean'
    ];

    private static $required_fields = [
        'name',
        'active'
    ];

    public function accounts(){
        return $this->hasMany('App\Models\Account', 'institution_id');
    }

    public static function find_institution_with_accounts($institution_id){
        $institution = Institution::with('accounts')->where('id', $institution_id);
        return $institution->first();
    }

    public static function getRequiredFieldsForUpdate(){
        return self::$required_fields;
    }

    public static function getRequiredFieldsForCreation(){
        return self::$required_fields;
    }

}