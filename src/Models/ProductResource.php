<?php

namespace Skeleton\Store\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductResource extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'title',
        'description',
        'resource_type',
        'resource_url',
        'file_path',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
