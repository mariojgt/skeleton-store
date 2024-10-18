<?php

namespace Skeleton\Store\Resource;

use Skeleton\Store\Models\Order;
use Skeleton\Store\Models\Product;
use Mariojgt\SkeletonAdmin\Helpers\Gravatar;
use Mariojgt\Magnifier\Resources\MediaResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        $media = $this->media?->map(function ($item) {
            return new MediaResource($item->media);
        }) ?? null;

        $isPurchase = false;

        if (auth()->user()) {
            $isPurchase = Order::where('user_id', auth()->user()->id)->where('status', 'completed')->whereHas('orderItems', function ($query) {
                $query->where('item_type', Product::class)->where('item_id', $this->id);
            })->first();
            $isPurchase = $isPurchase ? true : false;
        }

        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'description' => $this->description,
            'price'       => $this->price,
            'file_path'   => $this->file_path,
            'category_id' => $this->category_id,
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
            'media'       => $media,
            'type'        => $this->type,
            'price_type'  => $this->price_type,
            'is_purchase' => $isPurchase,
        ];
    }
}
