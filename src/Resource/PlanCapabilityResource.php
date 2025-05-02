<?php

namespace Skeleton\Store\Resource;

use Illuminate\Http\Resources\Json\JsonResource;

class PlanCapabilityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

        return [
            'name' => $this->capability->name,
            'type' => $this->capability->slug,
            'is_unlimited' => $this->is_unlimited,
            'usage_limit' => $this->usage_limit,
            'restriction_type' => $this->restriction_type,
            'initial_credits' => $this->initial_credits,
        ];
    }
}
