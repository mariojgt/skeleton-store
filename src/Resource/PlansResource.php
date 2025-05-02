<?php

namespace Skeleton\Store\Resource;

use Illuminate\Http\Resources\Json\JsonResource;
use Skeleton\Store\Resource\PlanCapabilityResource;

class PlansResource extends JsonResource
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
        // Calculate yearly prices
        $yearlyPrice = null;
        $yearlyDiscount = null;

        if ($this->auto_renew) {
            $yearlyPrice = $this->price * 12;
            $yearlyDiscount = $yearlyPrice * 0.2; // 20% discount
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'duration' => $this->duration,
            'duration_type' => $this->duration_type,
            'is_active' => $this->is_active,
            'product_id' => $this->product_id,
            'auto_renew' => $this->auto_renew,
            'yearlyPrice' => $yearlyPrice ? $yearlyPrice - $yearlyDiscount : null,
            'yearlyOriginalPrice' => $yearlyPrice,
            'yearlyDiscount' => $yearlyDiscount,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            // Include capabilities with the plan
            'capabilities' => PlanCapabilityResource::collection($this->planCapabilities),
        ];
    }
}
