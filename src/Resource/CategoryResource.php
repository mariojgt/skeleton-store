<?php

namespace Skeleton\Store\Resource;

use Illuminate\Support\Facades\Cache;
use Mariojgt\Magnifier\Resources\MediaResource;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
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

        if ($this->media?->first()) {
            $media = [new MediaResource($this->media->first()->media)];
        } else {
            $media = null;
        }

        $productCount = Cache::remember('category.product.count.' . $this->id, 60, function () {
            return $this->products->count();
        });

        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'slug'          => $this->slug,
            'icon'          => $this->svg,
            'media'         => $media,
            'product_count' => $productCount,
        ];
    }
}
