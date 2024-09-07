<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Mostafaznv\LaraCache\CacheEntity;
use Mostafaznv\LaraCache\Traits\LaraCache;

class Tag extends BaseModel {
    use HasFactory;
    use LaraCache;

    // variables
    protected $table = 'tags';
    public $timestamps = false; // turns off default laravel timestamping
    protected $fillable = [
        'name',
    ];
    protected $guarded = [
        'id',
    ];

    public function entries() {
        return $this->belongsToMany('App\Models\Entry', 'entry_tags', 'tag_id', 'entry_id');
    }

    public static function cacheEntities(): array {
        return [
            CacheEntity::make('all')->forever()->cache(function() {
                return Tag::all();
            }),
            CacheEntity::make('count')->forever()->cache(function() {
                return Tag::count();
            }),
        ];
    }

}
