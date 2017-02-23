<?php

namespace App;

class Entry extends BaseModel {

    const CREATED_AT = 'create_stamp';
    const UPDATED_AT = 'modified_stamp';

    protected $table = 'entries';
    protected $fillable = [
        'entry_date', 'account_type', 'entry_value', 'memo', 'expense', 'confirm', 'deleted'
    ];
    protected $guarded = [
        'id', 'create_stamp', 'modified_stamp'
    ];

    private static $required_entry_fields = [
        'account_type',
        'confirm',
        'deleted',
        'entry_date',
        'entry_value',
        'expense',
        'memo'
    ];

    /**
     * entries.account_type = account_types.id
     */
    public function account_type(){
        return $this->belongsTo('App\AccountType', 'account_type');
    }

    /**
     * entries.id = entry_tags.entry_id
     * entry_tags.tag_id = tags.id
     */
    public function tags(){
        return $this->belongsToMany('App\Tag', 'entry_tags', 'entry_id', 'tag_id');
    }

    /**
     * attachments.entry_id = entries.id
     */
    public function attachments(){
        return $this->hasMany('App\Attachment');
    }

    public function save(array $options = []){
        $saved_entry = parent::save($options);
        $actual_entry_value = (($this->expense) ? -1 : 1)*$this->entry_value;
        $this->account_type()->first()->account()->first()->update_total($actual_entry_value);
        return $saved_entry;
    }

    public static function get_entry_with_tags_and_attachments($entry_id){
        return Entry::with(['tags', 'attachments'])
            ->where('id', $entry_id)
            ->first();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function get_collection_of_non_deleted_entries(){
        return Entry::where('deleted', 0)->get();
    }

    /**
     * @return int
     */
    public static function count_non_deleted_entries(){
        return Entry::where('deleted', 0)->count();
    }

    /**
     * @return bool
     */
    public function has_attachments(){
        return $this->attachments()->count() > 0;
    }

    /**
     * @return array
     */
    public function get_tag_ids(){
        $collection_of_tags = $this->tags()->getResults();
        if(is_null($collection_of_tags) || $collection_of_tags->isEmpty()){
            return [];
        } else {
            return $collection_of_tags->pluck('pivot.tag_id')->toArray();
        }
    }

    public static function get_fields_required_for_creation(){
        $fields = self::$required_entry_fields;
        unset($fields[array_search('deleted', $fields)]);
        // using array_values here to reset the array index
        // after we unset the "deleted" element
        return array_values($fields);
    }

    public static function get_fields_required_for_update(){
        return self::$required_entry_fields;
    }

}