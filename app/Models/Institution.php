<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mostafaznv\LaraCache\CacheEntity;
use Mostafaznv\LaraCache\Traits\LaraCache;

class Institution extends BaseModel {
    use HasFactory;
    use LaraCache;
    use SoftDeletes;

    const CACHE_KEY_ALL = 'all';
    const CACHE_KEY_COUNT = 'count';

    const CREATED_AT = 'create_stamp';
    const UPDATED_AT = 'modified_stamp';
    const DELETED_AT = 'disabled_stamp';

    protected $table = 'institutions';
    protected $fillable = [
        'name'
    ];
    protected $guarded = [
        'id'
    ];
    protected $appends = [
        'active'
    ];
    private static $required_fields = [
        'name',
    ];

    public function accounts() {
        return $this->hasMany(Account::class, 'institution_id')
            ->withTrashed()->get();
    }

    public function getActiveAttribute() {
        return is_null($this->{self::DELETED_AT});
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
                    return Institution::withTrashed()->get();
                }),
            CacheEntity::make(self::CACHE_KEY_COUNT)
                ->forever()
                ->cache(function() {
                    return Institution::withTrashed()->count();
                })
        ];
    }

}
