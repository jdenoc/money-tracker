<?php

namespace App\Models;

use App\Jobs\AdjustAccountTotalUsingAccountType;
use App\Traits\EntryFilterKeys;
use Brick\Money\Money;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class Entry extends BaseModel {
    use EntryFilterKeys;
    use HasFactory;

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
        'disabled'=>'boolean',
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
    public function accountType() {
        return $this->belongsTo(AccountType::class, 'account_type_id');
    }

    /**
     * @deprecated
     *
     * alias for accountType
     * needed to support legacy implementations
     */
    public function account_type() {
        return $this->accountType();
    }

    /**
     * entries.id = entry_tags.entry_id
     * entry_tags.tag_id = tags.id
     */
    public function tags() {
        return $this->belongsToMany('App\Models\Tag', 'entry_tags', 'entry_id', 'tag_id');
    }

    /**
     * attachments.entry_id = entries.id
     */
    public function attachments() {
        return $this->hasMany('App\Models\Attachment');
    }

    public function getEntryValueAttribute($value) {
        return Money::ofMinor($value, $this->currency)->getAmount()->toFloat();
    }

    public function setEntryValueAttribute($value) {
        $entry_value = Money::of($value, $this->currency);
        $this->attributes['entry_value'] = $entry_value->getMinorAmount()->toInt();
    }

    public function getCurrencyAttribute() {
        return $this->accountType ? ($this->accountType->account ? $this->accountType->account->currency : Currency::DEFAULT_CURRENCY_CODE) : Currency::DEFAULT_CURRENCY_CODE;
    }

    public function save(array $options = []) {
        if ($this->exists) {
            // if the entry already exists
            // remove that original value from the total of originally associated account
            $original_account_type_id = $this->getOriginal('account_type_id');
            $original_raw_entry_value = $this->getRawOriginal('entry_value');
            $original_is_expense = $this->getOriginal('expense');
            AdjustAccountTotalUsingAccountType::dispatch($original_account_type_id, $original_raw_entry_value, $original_is_expense, false);
        }

        $saved_entry = parent::save($options);

        if (!$this->disabled) {
            // add new entry value to account total
            $current_account_type_id = $this->account_type_id;
            $currenct_raw_entry_value = $this->attributes['entry_value'];
            $current_is_expense = $this->expense;
            AdjustAccountTotalUsingAccountType::dispatch($current_account_type_id, $currenct_raw_entry_value, $current_is_expense, true);
        }
        return $saved_entry;
    }

    public function disable() {
        $this->disabled = true;
        $this->disabled_stamp = new Carbon();
        $this->save();
    }

    /**
     * @param array $filters
     * @param int $limit
     * @param int $offset
     * @param string $sort_by
     * @param string $sort_direction
     * @return \Illuminate\Support\Collection
     */
    public static function get_collection_of_non_disabled_entries(array $filters = [], int $limit=10, int $offset=0, string $sort_by=self::DEFAULT_SORT_PARAMETER, string $sort_direction=self::DEFAULT_SORT_DIRECTION) {
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
    public static function count_non_disabled_entries(array $filters = []): int {
        $entries_query = self::build_entry_query($filters);
        // due to the risk of failure with potentially adding GROUP BY to the query
        // we're going to use the generated query as a subquery and count from that
        return DB::table($entries_query->select('entries.id'))->count();
    }

    /**
     * @param array $filters
     * @return mixed
     */
    private static function build_entry_query(array $filters) {
        $entries_query = Entry::where('entries.disabled', 0);
        return self::filter_entry_collection($entries_query, $filters);
    }

    /**
     * @param Builder $entries_query
     * @param array $filters
     * @return mixed
     */
    private static function filter_entry_collection($entries_query, array $filters) {
        foreach ($filters as $filter_name => $filter_constraint) {
            switch($filter_name) {
                case self::$FILTER_KEY_START_DATE:
                    $entries_query->where('entries.entry_date', '>=', $filter_constraint);
                    break;
                case self::$FILTER_KEY_MIN_VALUE:
                    $entries_query->where('entries.entry_value', '>=', Money::of($filter_constraint, Currency::DEFAULT_CURRENCY_CODE)->getMinorAmount()->toInt());
                    break;
                case self::$FILTER_KEY_END_DATE:
                    $entries_query->where('entries.entry_date', '<=', $filter_constraint);
                    break;
                case self::$FILTER_KEY_MAX_VALUE:
                    $entries_query->where('entries.entry_value', '<=', Money::of($filter_constraint, Currency::DEFAULT_CURRENCY_CODE)->getMinorAmount()->toInt());
                    break;
                case self::$FILTER_KEY_ACCOUNT_TYPE:
                    $entries_query->where('entries.account_type_id', $filter_constraint);
                    break;
                case self::$FILTER_KEY_EXPENSE:
                    if ($filter_constraint === true) {
                        $entries_query->where('entries.expense', 1);
                    } elseif ($filter_constraint === false) {
                        $entries_query->where('entries.expense', 0);
                    }
                    break;
                case self::$FILTER_KEY_UNCONFIRMED:
                    if ($filter_constraint === true) {
                        $entries_query->where('entries.confirm', 0);
                    }
                    break;
                case self::$FILTER_KEY_ACCOUNT:
                    $entries_query->join('account_types', static function($join) use ($filter_constraint) {
                        $join->on('entries.account_type_id', '=', 'account_types.id')
                            ->where('account_types.account_id', $filter_constraint);
                    });
                    break;
                case self::$FILTER_KEY_ATTACHMENTS:
                    if ($filter_constraint === true) {
                        // to get COUNT(attachment.id) > 0 use:
                        // INNER JOIN attachments ON attachments.entry_id=entries.id
                        $entries_query->join('attachments', 'entries.id', '=', 'attachments.entry_id');
                    } elseif ($filter_constraint === false) {
                        // to get COUNT(attachments.id) == 0 use:
                        // LEFT JOIN attachments
                        //   ON attachments.entry_id=entries.id
                        // WHERE attachments.entry_id IS NULL
                        $entries_query->leftJoin('attachments', 'entries.id', '=', 'attachments.entry_id')
                            ->whereNull('attachments.entry_id');
                    }
                    break;
                case self::$FILTER_KEY_TAGS:
                    // RIGHT JOIN entry_tags
                    //   ON entry_tags.entry_id=entries.id
                    //   AND entry_tags.tag_id IN ($tag_ids)
                    $tag_ids = (is_array($filter_constraint)) ? $filter_constraint : [$filter_constraint];
                    $entries_query->rightJoin('entry_tags', static function($join) use ($tag_ids) {
                        $join->on('entry_tags.entry_id', '=', 'entries.id')
                            ->whereIn('entry_tags.tag_id', $tag_ids);
                    })
                        ->groupBy('entries.id')
                        ->havingRaw('count(entries.id) >= ?', [count($tag_ids)]);
                    break;
                case self::$FILTER_KEY_IS_TRANSFER:
                    if ($filter_constraint === true) {
                        // WHERE entries.transfer_entry_id IS NOT NULL
                        $entries_query->whereNotNull("transfer_entry_id");
                    } elseif ($filter_constraint === false) {
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
    public function has_attachments(): bool {
        try {
            return $this->attachments()->count() > 0;
        } catch (\Exception $e) {
            error_log($e);
            return false;
        }
    }

    public function has_tags(): bool {
        try {
            return $this->tags()->count() > 0;
        } catch(\Exception $e) {
            error_log($e);
            return false;
        }
    }

    /**
     * @return array
     */
    public function get_tag_ids(): array {
        try {
            $collection_of_tags = $this->tags()->getResults();
        } catch (\Exception $e) {
            error_log($e);
            return [];
        }
        if (is_null($collection_of_tags) || $collection_of_tags->isEmpty()) {
            return [];
        } else {
            return $collection_of_tags->pluck('pivot.tag_id')->toArray();
        }
    }

    public static function get_fields_required_for_creation(): array {
        $fields = self::$required_entry_fields;
        unset($fields[array_search('disabled', $fields)]);
        // using array_values here to reset the array index
        // after we unset the "disabled" element
        return array_values($fields);
    }

    public static function get_fields_required_for_update(): array {
        return self::$required_entry_fields;
    }

}
