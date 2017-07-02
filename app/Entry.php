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
    protected $casts = [
        'expense'=>'boolean',
        'confirm'=>'boolean',
        'deleted'=>'boolean'
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
        if($this->exists){
            // if the entry already exists
            // remove that original value from the account total
            $actual_entry_value = ($this->getOriginal('expense') ? -1 : 1)*$this->getOriginal('entry_value');
            $this->account_type()->first()->account()->first()->update_total(-1*$actual_entry_value);
        }

        $saved_entry = parent::save($options);

        if(!$this->deleted){
            // add new entry value to account total
            $actual_entry_value = (($this->expense) ? -1 : 1) * $this->entry_value;
            $this->account_type()->first()->account()->first()->update_total($actual_entry_value);
        }
        return $saved_entry;
    }

    public static function get_entry_with_tags_and_attachments($entry_id){
        return Entry::with(['tags', 'attachments'])
            ->where('id', $entry_id)
            ->first();
    }

    /**
     * @param array $filters
     * @param int $limit
     * @param int $offset
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function get_collection_of_non_deleted_entries($filters = [], $limit=10, $offset=0){
        $entries_query = Entry::where('deleted', 0);
        $entries_query = self::filter_entry_collection($entries_query, $filters);
        $entries_query->select("entries.*");    // this makes sure that the correct ID is present if a JOIN is required
        return $entries_query->offset($offset)->limit($limit)->get();
    }

    /**
     * @param array $filters
     * @return int
     */
    public static function count_non_deleted_entries($filters = []){
        $entries_query = Entry::where('deleted', 0);
        $entries_query = self::filter_entry_collection($entries_query, $filters);
        return $entries_query->count();
    }

    private static function filter_entry_collection($entries_query, $filters){
        $expense_values = [];
        foreach($filters as $filter_name => $filter_constraint){
            switch($filter_name){
                case 'start_date':
                    $entries_query->where('entry_date', '>=', $filter_constraint);
                    break;
                case 'entry_value_min':
                    $entries_query->where('entry_value', '>=', $filter_constraint);
                    break;
                case 'end_date':
                    $entries_query->where('entry_date', '<=', $filter_constraint);
                    break;
                case 'entry_value_max':
                    $entries_query->where('entry_value', '<=', $filter_constraint);
                    break;
                case 'account_type':
                    $entries_query->where('account_type', $filter_constraint);
                    break;
                case 'income':
                    if($filter_constraint == true){
                        $expense_values[] = 0;
                    }
                    break;
                case 'expense':
                    if($filter_constraint == true){
                        $expense_values[] = 1;
                    }
                    break;
                case 'not_confirmed':
                    if($filter_constraint == true){
                        $entries_query->where('confirm', 0);
                    }
                    break;
                case 'has_attachments':
                    if($filter_constraint == true){
                        // to get COUNT(attachment.id) > 0 use:
                        // INNER JOIN attachments ON attachments.entry_id=entries.id
                        $entries_query->join('attachments', 'entries.id', '=', 'attachments.entry_id');
                    } elseif($filter_constraint == false) {
                        // to get COUNT(attachments.id) == 0 use:
                        // LEFT JOIN attachments
                        //   ON attachments.entry_id=entries.id
                        //   AND attachments.entry_id IS NULL
                        $entries_query->leftJoin('attachments', function($join){
                            $join->on('entries.id', '=', 'attachments.entry_id')
                                ->whereNull('attachments.entry_id');
                        });
                    }
                    break;
                case 'tags':
                    // LEFT JOIN entry_tags
                    //   ON entry_tags.entry_id=entries.id
                    //   AND entry_tags.tag_id IN ($tags)
                    $tag_ids = (is_array($filters[$filter_name])) ? $filter_constraint : [$filter_constraint];
                    $entries_query->leftJoin('entry_tags', function($join) use ($tag_ids){
                        $join->on('entry_tags.entry_id', '=', 'entries.id')
                            ->whereIn('entry_tags.tag_id', $tag_ids);
                    });
                    break;
            }
        }

        if(!empty($expense_values)){
            $entries_query->whereIn('expense', $expense_values);
        }

        return $entries_query;
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