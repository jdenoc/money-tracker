<?php

namespace App\Models;

use Brick\Money\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;
use Mostafaznv\LaraCache\CacheEntity;
use Mostafaznv\LaraCache\Traits\LaraCache;

class Account extends BaseModel {
    use HasFactory;
    use LaraCache;

    const CREATED_AT = 'create_stamp';
    const UPDATED_AT = 'modified_stamp';

    protected $table = 'accounts';
    protected $fillable = [
        'name', 'institution_id' ,'total'
    ];
    protected $guarded = [
        'id'
    ];
    protected $casts = [
        'disabled'=>'boolean',
    ];
    protected $dates = [
        'disabled_stamp'
    ];
    private static $required_fields = [
        'name',
        'institution_id',
        'disabled',
        'total',
        'currency',
    ];

    public function institution() {
        return $this->belongsTo(Institution::class, 'institution_id');
    }

    public function account_types() {
        return $this->hasMany('App\Models\AccountType', 'account_id');
    }

    public function getTotalAttribute($value) {
        return Money::ofMinor($value, $this->currency)->getAmount()->toFloat();
    }

    public function setTotalAttribute($value) {
        $entry_value = Money::of($value, $this->currency);
        $this->attributes['total'] = $entry_value->getMinorAmount()->toInt();
    }

    public function save(array $options = []) {
        if (!$this->getOriginal('disabled') && $this->disabled) {
            $this->disabled_stamp = new Carbon();
        }
        return parent::save($options);
    }

    public function addToTotal(Money $value) {
        $account_total = Money::ofMinor($this->attributes['total'], $this->currency);
        $this->total = $account_total->plus($value)->getAmount()->toFloat();
        $this->save();
    }

    public function subtractFromTotal(Money $value) {
        $this->addToTotal($value->multipliedBy(-1));
    }

    public static function find_account_with_types($account_id) {
        $account = Account::with('account_types')->where('id', $account_id);
        return $account->first();
    }

    public static function getRequiredFieldsForUpdate() {
        return self::$required_fields;
    }

    public static function getRequiredFieldsForCreation() {
        return self::$required_fields;
    }

    public static function cacheEntities(): array {
        return [
            CacheEntity::make('all')->forever()->cache(function() {
                return Account::all();
            }),
            CacheEntity::make('count')->forever()->cache(function() {
                return Account::count();
            })
        ];
    }

}
