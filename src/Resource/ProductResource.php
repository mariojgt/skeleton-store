<?php

namespace Skeleton\Store\Resource;

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
        if ($this->media?->first()) {
            $media = [new MediaResource($this->media->first()->media)];
        } else {
            $media = null;
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
        ];
    }
}
