<?php

namespace Skeleton\Store\Resource;

use Mariojgt\SkeletonAdmin\Helpers\Gravatar;
use Mariojgt\Magnifier\Resources\MediaResource;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
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
        return [
            'id'            => $this->id,
            'plan'          => $this->plan,
            'start_date'    => $this->start_date,
            'start_date_at' => $this->start_date->format('F j, Y'),
            'end_date'      => $this->end_date,
            'renovation_at' => $this->end_date->format('F j, Y'),
            'status'        => $this->status,
            'duration_left' => $this->durationLeft(),
        ];
    }
}
