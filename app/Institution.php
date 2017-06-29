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
    protected $dates = [
        'create_stamp',
        'modified_stamp'
    ];

    public function accounts(){
        return $this->hasMany('App\Account', 'institution_id');
    }

    public static function find_institution_with_accounts($institution_id){
        $institution = Institution::with('accounts')->where('id', $institution_id);
        return $institution->first();
    }

}