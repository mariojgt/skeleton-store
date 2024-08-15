<?php

namespace Skeleton\Store\Resource;

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
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'media'       => $media,
        ];
    }
}
