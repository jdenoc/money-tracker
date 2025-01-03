<?php

namespace App\Models;

use App\Jobs\AdjustAccountTotalUsingAccountType;
use App\Traits\EntryFilterKeys;
use Brick\Money\Money;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Entry extends BaseModel {
    use EntryFilterKeys;
    use HasFactory;
    use SoftDeletes;

    // timestamp table columns
    const CREATED_AT = 'create_stamp';
    const UPDATED_AT = 'modified_stamp';
    const DELETED_AT = 'disabled_stamp';

    // filter keys
    const DEFAULT_SORT_PARAMETER = 'id';
    const DEFAULT_SORT_DIRECTION = 'desc';
    const SORT_DIRECTION_ASC = 'asc';
    const SORT_DIRECTION_DESC = 'desc';

    // variables
    protected $table = 'entries';
    protected $fillable = [
        'entry_date', 'account_type_id', 'entry_value', 'memo', 'expense', 'confirm', 'transfer_entry_id',
    ];
    protected $guarded = [
        'id', 'create_stamp', 'modified_stamp',
    ];
    protected $casts = [
        'expense' => 'boolean',
        'confirm' => 'boolean',
    ];
    private static $required_entry_fields = [
        'account_type_id',
        'confirm',
        'entry_date',
        'entry_value',
        'expense',
        'memo',
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
        return $this->belongsToMany(Tag::class, 'entry_tags', 'entry_id', 'tag_id');
    }

    /**
     * attachments.entry_id = entries.id
     */
    public function attachments() {
        return $this->hasMany(Attachment::class);
    }

    protected function entryValue(): Attribute {
        return Attribute::make(
            get: function($value) {
                return Money::ofMinor($value, $this->currency)->getAmount()->toFloat();
            },
            set: function($value) {
                $entry_value = Money::of($value, $this->currency);
                return $entry_value->getMinorAmount()->toInt();
            }
        );
    }

    protected function currency(): Attribute {
        return Attribute::make(
            get: function($value) {
                try {
                    if($this->accountType) {
                        return $this->accountType->account ? $this->accountType->account->currency : Currency::DEFAULT_CURRENCY_CODE;
                    } else {
                        return Currency::DEFAULT_CURRENCY_CODE;
                    }
                } catch (\Exception $e) {
                    error_log($e);
                    return Currency::DEFAULT_CURRENCY_CODE;
                }
            }
        );
    }

    protected function hasAttachments(): Attribute {
        return Attribute::make(
            get: function($value): bool {
                try {
                    return $this->attachments()->count() > 0;
                } catch (\Exception $e) {
                    error_log($e);
                    return false;
                }
            }
        );
    }

    protected function hasTags(): Attribute {
        return Attribute::make(
            get: function($value): bool {
                try {
                    return $this->tags()->count() > 0;
                } catch (\Exception $e) {
                    error_log($e);
                    return false;
                }
            }
        );
    }

    protected function tagIds(): Attribute {
        return Attribute::make(
            get: function($value) {
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
        );
    }

    public function delete() {
        $this->removeEntryValueFromAccountTotal();
        return parent::delete();
    }

    public function save(array $options = []) {
        if ($this->exists) {
            // if the entry already exists
            // remove that original value from the total of originally associated account
            $this->removeEntryValueFromAccountTotal();
        }

        $saved_entry = parent::save($options);
        $this->addEntryValueToAccountTotal();

        return $saved_entry;
    }

    private function addEntryValueToAccountTotal(): void {
        $current_account_type_id = $this->account_type_id;
        $current_raw_entry_value = $this->attributes['entry_value'];
        $current_is_expense = $this->expense;
        AdjustAccountTotalUsingAccountType::dispatch($current_account_type_id, $current_raw_entry_value, $current_is_expense, true);
    }

    private function removeEntryValueFromAccountTotal(): void {
        $original_account_type_id = $this->getOriginal('account_type_id');
        $original_raw_entry_value = $this->getRawOriginal('entry_value');
        $original_is_expense = $this->getOriginal('expense');
        AdjustAccountTotalUsingAccountType::dispatch($original_account_type_id, $original_raw_entry_value, $original_is_expense, false);
    }

    /**
     * @param array $filters
     * @param int $limit
     * @param int $offset
     * @param string $sort_by
     * @param string $sort_direction
     * @return \Illuminate\Support\Collection
     */
    public static function get_collection_of_entries(array $filters = [], int $limit = 10, int $offset = 0, string $sort_by = self::DEFAULT_SORT_PARAMETER, string $sort_direction = self::DEFAULT_SORT_DIRECTION) {
        $entries_query = self::filter_entry_collection($filters);
        $entries_query
            ->distinct()->select("entries.*")
            ->selectSub("SELECT GROUP_CONCAT(COALESCE(entry_tags.tag_id, '')) FROM entry_tags WHERE entry_tags.entry_id=entries.id", "tags")
            ->withExists("attachments")
            ->orderBy($sort_by, $sort_direction)
            ->latest(self::CREATED_AT);
        return $entries_query->offset($offset)->limit($limit)->get();
    }

    /**
     * @param array $filters
     * @return int
     */
    public static function count_collection_of_entries(array $filters = []): int {
        $entries_query = self::filter_entry_collection($filters);
        return $entries_query->select('entries.id')->count();
    }

    /**
     * @param array $filters
     */
    private static function filter_entry_collection(array $filters) {
        $entries_query = Entry::query();
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

    public static function get_fields_required_for_creation(): array {
        return self::$required_entry_fields;
    }

    public static function get_fields_required_for_update(): array {
        return self::$required_entry_fields;
    }

}
