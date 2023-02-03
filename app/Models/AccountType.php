<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mostafaznv\LaraCache\CacheEntity;
use Mostafaznv\LaraCache\Traits\LaraCache;

class AccountType extends BaseModel {
    use HasFactory;
    use LaraCache;
    use SoftDeletes;

    const CACHE_KEY_ALL = 'all';
    const CACHE_KEY_COUNT = 'count';
    const CACHE_KEY_TYPES = 'types';

    const CREATED_AT = 'create_stamp';
    const UPDATED_AT = 'modified_stamp';
    const DELETED_AT = 'disabled_stamp';

    protected $table = 'account_types';
    protected $fillable = [
        'type', 'last_digits', 'name', 'account_id'
    ];
    protected $guarded = [
        'id', 'modified_stamp'
    ];
    protected $appends = [
        'active'
    ];
    private static $required_fields = [
        'name',
        'account_id',
        'type',
        'last_digits',
    ];

    public function account() {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function entries() {
        return $this->hasMany(Entry::class, 'account_type_id');
    }

    public function getActiveAttribute() {
        return is_null($this->{self::DELETED_AT});
    }

    public static function getEnumValues() {
        return parent::get_enum_values('type');
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
                    return AccountType::all();
                }),
            CacheEntity::make(self::CACHE_KEY_COUNT)
                ->forever()
                ->cache(function() {
                    return AccountType::count();
                }),
            CacheEntity::make(self::CACHE_KEY_TYPES)
                ->forever()
                ->cache(function() {
                    return AccountType::getEnumValues();
                })
        ];
    }

}
