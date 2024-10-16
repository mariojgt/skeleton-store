<?php

namespace App\Helpers;

use Skeleton\Store\Models\Product;

class SkeletonStoreHelper
{
    public static function findProduct($product): array
    {
        if ($product['type'] === 'product') {
            $item = Product::findOrFail($product['id']);
            return [
                'name'   => $item->name,
                'amount' => $item->price,
                'model'  => $item,
                'media_url' => ['https://placehold.co/600x400'],
            ];
        }
    }
}
