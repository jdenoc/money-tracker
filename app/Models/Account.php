<?php

namespace App\Models;

use Brick\Money\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mostafaznv\LaraCache\CacheEntity;
use Mostafaznv\LaraCache\Traits\LaraCache;

class Account extends BaseModel {
    use HasFactory;
    use LaraCache;
    use SoftDeletes;

    // cache constants
    const CACHE_KEY_ALL = 'all';
    const CACHE_KEY_COUNT = 'count';

    // timestamp table columns
    const CREATED_AT = 'create_stamp';
    const UPDATED_AT = 'modified_stamp';
    const DELETED_AT = 'disabled_stamp';

    // variables
    protected $table = 'accounts';
    protected $attributes = [
        'currency' => 'USD',
        'total' => 0,
    ];
    protected $fillable = [
        'name', 'institution_id' ,'total',
    ];
    protected $guarded = [
        'id',
    ];
    protected $appends = [
        'active',
    ];
    private static $required_fields = [
        'name',
        'institution_id',
        'total',
        'currency',
    ];

    public function institution() {
        return $this->belongsTo(Institution::class, 'institution_id');
    }

    public function accountTypes() {
        return $this->hasMany(AccountType::class, 'account_id');
    }

    /**
     * @deprecated
     *
     * alias for accountTypes
     * needed to support legacy implementations
     */
    public function account_types() {
        return $this->accountTypes();
    }

    public function getActiveAttribute() {
        return is_null($this->{self::DELETED_AT});
    }

    public function getTotalAttribute($value) {
        return Money::ofMinor($value, $this->currency)->getAmount()->toFloat();
    }

    public function setTotalAttribute($value) {
        $entry_value = Money::of($value, $this->currency);
        $this->attributes['total'] = $entry_value->getMinorAmount()->toInt();
    }

    public function addToTotal(Money $value) {
        $account_total = Money::ofMinor($this->attributes['total'], $this->currency);
        $this->total = $account_total->plus($value)->getAmount()->toFloat();
        $this->save();
    }

    public function subtractFromTotal(Money $value) {
        $this->addToTotal($value->multipliedBy(-1));
    }

    public static function getRequiredFieldsForUpdate() {
        return self::$required_fields;
    }

    public static function getRequiredFieldsForCreation() {
        return self::$required_fields;
    }

    public static function cacheEntities(): array {
        return [
            CacheEntity::make(self::CACHE_KEY_ALL)
                ->forever()
                ->cache(function() {
                    return Account::withTrashed()->get();
                }),
            CacheEntity::make(self::CACHE_KEY_COUNT)
                ->forever()
                ->cache(function() {
                    return Account::withTrashed()->count();
                }),
        ];
    }

}
