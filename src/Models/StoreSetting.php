<?php

namespace Skeleton\Store\Models;

use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StoreSetting extends BaseMasterModel
{
    use HasFactory;
    use softDeletes;

    protected $fillable = ['value', 'key'];

    protected $casts = [
        'value' => 'string',
        'key'   => 'string',
    ];

    // on the create or update of a store setting we want to clear the cache
    protected static function boot()
    {
        parent::boot();

        static::created(function () {
            Cache::forget('skeletonStore');
        });

        static::updated(function () {
            Cache::forget('skeletonStore');
        });

        static::deleted(function () {
            Cache::forget('skeletonStore');
        });
    }
}
