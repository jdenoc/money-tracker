<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Entry extends Model {

    const CREATED_AT = 'create_stamp';
    const UPDATED_AT = 'modified_stamp';

    protected $table = 'entries';
    protected $fillable = [
        'entry_date', 'account_type', 'entry_value', 'memo', 'expense', 'confirm', 'deleted'
    ];
    protected $guarded = [
        'id', 'create_stamp', 'modified_stamp'
    ];

    public function account_type(){
        return $this->belongsTo('App\AccountType', 'account_type');
    }

    public function tags(){
        return $this->belongsToMany('App\Tag', 'entry_tags', 'entry_id', 'tag_id');
    }

    public function attachments(){
        return $this->hasMany('App\Attachment');
    }

}