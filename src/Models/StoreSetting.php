<?php

namespace Skeleton\Store\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class StoreSetting extends BaseMasterModel
{
    use HasFactory;
    use softDeletes;

    protected $fillable = ['value', 'key'];

    protected $casts = [
        'value' => 'string',
        'key'   => 'string',
    ];
}
