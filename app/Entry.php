<?php

namespace App;

use App\Http\Controllers\Api\EntryController;
use Carbon\Carbon;

class Entry extends BaseModel {

    const CREATED_AT = 'create_stamp';
    const UPDATED_AT = 'modified_stamp';

    const DEFAULT_SORT_PARAMETER = 'id';
    const DEFAULT_SORT_DIRECTION = 'desc';
    const SORT_DIRECTION_ASC = 'asc';
    const SORT_DIRECTION_DESC = 'desc';

    protected $table = 'entries';
    protected $fillable = [
        'entry_date', 'account_type_id', 'entry_value', 'memo', 'expense', 'confirm', 'disabled', 'disabled_stamp', 'transfer_entry_id'
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
        'account_type_id',
        'confirm',
        'disabled',
        'entry_date',
        'entry_value',
        'expense',
        'memo'
    ];

    /**
     * entries.account_type_id = account_types.id
     */
    public function account_type(){
        return $this->belongsTo('App\AccountType', 'account_type_id');
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
            // remove that original value from the total of originally associated account
            $actual_entry_value = ($this->getOriginal('expense') ? -1 : 1)*$this->getOriginal('entry_value');
            $original_account_type_id = $this->getOriginal('account_type_id');
            $original_account_type = AccountType::find($original_account_type_id);
            $original_account_type->account()->first()->update_total(-1*$actual_entry_value);
        }

        $saved_entry = parent::save($options);

        if(!$this->disabled){
            // add new entry value to account total
            $actual_entry_value = (($this->expense) ? -1 : 1) * $this->entry_value;
            $this->account_type()->first()->account()->first()->update_total($actual_entry_value);
        }
        return $saved_entry;
    }

    public function disable(){
        $this->disabled = true;
        $this->disabled_stamp = new Carbon();
        $this->save();
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
     * @param string $sort_by
     * @param string $sort_direction
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function get_collection_of_non_disabled_entries($filters = [], $limit=10, $offset=0, $sort_by=self::DEFAULT_SORT_PARAMETER, $sort_direction=self::DEFAULT_SORT_DIRECTION){
        $entries_query = self::build_entry_query($filters);
        // this makes sure that the correct ID is present if a JOIN is required
        $entries_query->distinct()->select("entries.*");
        $entries_query->orderBy($sort_by, $sort_direction);
        $entries_query->latest(self::CREATED_AT);
        return $entries_query->offset($offset)->limit($limit)->get();
    }

    /**
     * @param array $filters
     * @return int
     */
    public static function count_non_disabled_entries($filters = []){
        $entries_query = self::build_entry_query($filters);
        return $entries_query->distinct()->count("entries.id");
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
                case EntryController::FILTER_KEY_START_DATE:
                    $entries_query->where('entries.entry_date', '>=', $filter_constraint);
                    break;
                case EntryController::FILTER_KEY_MIN_VALUE:
                    $entries_query->where('entries.entry_value', '>=', $filter_constraint);
                    break;
                case EntryController::FILTER_KEY_END_DATE:
                    $entries_query->where('entries.entry_date', '<=', $filter_constraint);
                    break;
                case EntryController::FILTER_KEY_MAX_VALUE:
                    $entries_query->where('entries.entry_value', '<=', $filter_constraint);
                    break;
                case EntryController::FILTER_KEY_ACCOUNT_TYPE:
                    $entries_query->where('entries.account_type_id', $filter_constraint);
                    break;
                case EntryController::FILTER_KEY_EXPENSE:
                    if($filter_constraint == true){
                        $entries_query->where('entries.expense', 1);
                    }
                    elseif($filter_constraint == false) {
                        $entries_query->where('entries.expense', 0);
                    }
                    break;
                case EntryController::FILTER_KEY_UNCONFIRMED:
                    if($filter_constraint == true){
                        $entries_query->where('entries.confirm', 0);
                    }
                    break;
                case EntryController::FILTER_KEY_ACCOUNT:
                    $entries_query->join('account_types', function($join) use ($filter_constraint){
                        $join->on('entries.account_type_id', '=', 'account_types.id')
                            ->where('account_types.account_id', $filter_constraint);
                    });
                    break;
                case EntryController::FILTER_KEY_ATTACHMENTS:
                    if($filter_constraint == true){
                        // to get COUNT(attachment.id) > 0 use:
                        // INNER JOIN attachments ON attachments.entry_id=entries.id
                        $entries_query->join('attachments', 'entries.id', '=', 'attachments.entry_id');
                    } elseif($filter_constraint == false) {
                        // to get COUNT(attachments.id) == 0 use:
                        // LEFT JOIN attachments
                        //   ON attachments.entry_id=entries.id
                        // WHERE attachments.entry_id IS NULL
                        $entries_query->leftJoin('attachments', 'entries.id', '=', 'attachments.entry_id')
                            ->whereNull('attachments.entry_id');
                    }
                    break;
                case EntryController::FILTER_KEY_TAGS:
                    // RIGHT JOIN entry_tags
                    //   ON entry_tags.entry_id=entries.id
                    //   AND entry_tags.tag_id IN ($tags)
                    $tag_ids = (is_array($filters[$filter_name])) ? $filter_constraint : [$filter_constraint];
                    $entries_query->rightJoin('entry_tags', function($join) use ($tag_ids){
                        $join->on('entry_tags.entry_id', '=', 'entries.id')
                            ->whereIn('entry_tags.tag_id', $tag_ids);
                    });
                    break;
                case EntryController::FILTER_KEY_IS_TRANSFER:
                    if($filter_constraint == true){
                        // WHERE entries.transfer_entry_id IS NOT NULL
                        $entries_query->whereNotNull("transfer_entry_id");
                    } elseif($filter_constraint == false){
                        // WHERE entries.transfer_entry_id IS NULL
                        $entries_query->whereNull("transfer_entry_id");
                    }
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

    public function has_tags(){
        try{
            return $this->tags()->count() > 0;
        } catch(\Exception $e){
            error_log($e);
            return false;
        }
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