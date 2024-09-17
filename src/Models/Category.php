<?php

namespace Skeleton\Store\Models;

use Illuminate\Database\Eloquent\Model;
use Mariojgt\SkeletonAdmin\Models\User;
use Mariojgt\SkeletonAdmin\Models\Admin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends BaseMasterModel
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['name', 'slug', 'svg'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
