<?php

namespace App;

class Entry extends BaseModel {

    const CREATED_AT = 'create_stamp';
    const UPDATED_AT = 'modified_stamp';

    protected $table = 'entries';
    protected $fillable = [
        'entry_date', 'account_type', 'entry_value', 'memo', 'expense', 'confirm', 'disabled', 'disabled_stamp'
    ];
    protected $guarded = [
        'id', 'create_stamp', 'modified_stamp'
    ];
    protected $casts = [
        'expense'=>'boolean',
        'confirm'=>'boolean',
        'disabled'=>'boolean'
    ];
    protected $dates = [
        'disabled_stamp'
    ];

    private static $required_entry_fields = [
        'account_type',
        'confirm',
        'disabled',
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

        if(!$this->disabled){
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
    public static function get_collection_of_non_disabled_entries($filters = [], $limit=10, $offset=0){
        $entries_query = self::build_entry_query($filters);
        $entries_query->select("entries.*");    // this makes sure that the correct ID is present if a JOIN is required
        return $entries_query->offset($offset)->limit($limit)->get();
    }

    /**
     * @param array $filters
     * @return int
     */
    public static function count_non_disabled_entries($filters = []){
        $entries_query = self::build_entry_query($filters);
        return $entries_query->count();
    }

    /**
     * @param array $filters
     * @return mixed
     */
    private static function build_entry_query($filters){
        $entries_query = Entry::where('entries.disabled', 0);
        return self::filter_entry_collection($entries_query, $filters);
    }

    private static function filter_entry_collection($entries_query, $filters){
        foreach($filters as $filter_name => $filter_constraint){
            switch($filter_name){
                case 'start_date':
                    $entries_query->where('entries.entry_date', '>=', $filter_constraint);
                    break;
                case 'min_value':
                    $entries_query->where('entries.entry_value', '>=', $filter_constraint);
                    break;
                case 'end_date':
                    $entries_query->where('entries.entry_date', '<=', $filter_constraint);
                    break;
                case 'max_value':
                    $entries_query->where('entries.entry_value', '<=', $filter_constraint);
                    break;
                case 'account_type':
                    $entries_query->where('entries.account_type', $filter_constraint);
                    break;
                case 'expense':
                    if($filter_constraint == true){
                        $entries_query->where('entries.expense', 1);
                    }
                    elseif($filter_constraint == false) {
                        $entries_query->where('entries.expense', 0);
                    }
                    break;
                case 'unconfirmed':
                    if($filter_constraint == true){
                        $entries_query->where('entries.confirm', 0);
                    }
                    break;
                case 'account':
                    $entries_query->join('account_types', function($join) use ($filter_constraint){
                        $join->on('entries.account_type', '=', 'account_types.id')
                            ->where('account_types.account_id', $filter_constraint);
                    });
                    break;
                case 'attachments':
                    // FIXME: displaying too many and duplicate entries
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
        unset($fields[array_search('disabled', $fields)]);
        // using array_values here to reset the array index
        // after we unset the "disabled" element
        return array_values($fields);
    }

    public static function get_fields_required_for_update(){
        return self::$required_entry_fields;
    }

}