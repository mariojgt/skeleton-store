<?php

namespace Skeleton\Store\Models;

use Illuminate\Database\Eloquent\Model;
use Mariojgt\SkeletonAdmin\Models\User;
use Mariojgt\SkeletonAdmin\Models\Admin;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends BaseMasterModel
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'svg'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
