<?php

namespace Skeleton\Store\Resource;

use Illuminate\Http\Resources\Json\JsonResource;
use Mariojgt\Magnifier\Resources\MediaResource;
use Mariojgt\SkeletonAdmin\Helpers\Gravatar;
use Skeleton\Store\Enums\CapabilityType;
use Skeleton\Store\Enums\ProductType;
use Skeleton\Store\Models\Order;
use Skeleton\Store\Models\Product;
use Skeleton\Store\Models\User;
use Skeleton\Store\Services\CapabilityService;

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
        $user = null;
        if (auth()->check()) {
            $user = User::find(auth()->user()->id);
        }
        // Check direct purchase
        $isPurchase = false;
        if ($user) {
            $isPurchase = Order::where('user_id', $user->id)
                ->where('status', 'completed')
                ->whereHas('orderItems', function ($query) {
                    $query->where('item_type', Product::class)
                         ->where('item_id', $this->id);
                })->first();
            $isPurchase = $isPurchase ? true : false;
        }

        // Check capability-based access
        $hasCapabilityAccess = false;
        $remainingUsage = 0;
        $capabilitySlug = null;
        $capabilityLabel = '';

        if ($user) {
            if ($this->free_with_subscription && $user->activeSubscription()) {
                // User has a subscription and the product is free with subscription
                $hasCapabilityAccess = true;
                $remainingUsage = -1; // Unlimited
            }

            // Determine which capability to check based on product type
            if ($this->type === ProductType::digital) {
                $capabilitySlug = CapabilityType::DIGITAL_RESOURCES->value;
                $capabilityLabel = 'Digital Resource';
            } elseif ($this->type === ProductType::project_templates) {
                $capabilitySlug = CapabilityType::PROJECT_TEMPLATES->value;
                $capabilityLabel = 'Project Template';
            }

            if ($capabilitySlug && !$this->free_with_subscription) {
                try {
                    $capabilityService = app(CapabilityService::class);

                    $hasCapabilityAccess = $capabilityService->userHasCapability($user, $capabilitySlug) &&
                                         $capabilityService->userCanUseCapability($user, $capabilitySlug);

                    $remainingUsage = $capabilityService->getRemainingUses($user, $capabilitySlug);
                } catch (\Exception $e) {
                    // Fallback to traditional subscription check if capability service fails
                    $hasCapabilityAccess = $this->free_with_subscription && $user->activeSubscription();
                }
            }
        }

        // Determine capability banner info
        $showCapabilityBanner = false;
        $bannerInfo = null;
        $isOutOfUsage = false;

        // Only show capability banners for products that aren't free with subscription
        if ($user && $capabilitySlug && !$isPurchase && !$this->free_with_subscription) {

            $hasSubscription = $user->activeSubscription();
            $isOutOfUsage = $hasCapabilityAccess && $remainingUsage === 0;

            // Show if has subscription but is out of usages
            if ($hasSubscription && $isOutOfUsage) {
                $showCapabilityBanner = true;
                $bannerInfo = [
                    'type' => 'depleted',
                    'title' => 'Usage limit reached',
                    'description' => "You've used all your available downloads for this billing cycle. Upgrade your subscription or wait for the next renewal."
                ];
            }
            // Show if has subscription with limited usage
            elseif ($hasSubscription && $hasCapabilityAccess && $remainingUsage > 0 && $remainingUsage !== -1) {
                $showCapabilityBanner = true;

                $bannerInfo = [
                    'type' => 'limited',
                    'title' => "$remainingUsage downloads remaining",
                    'description' => "Your subscription includes $remainingUsage more downloads this billing cycle."
                ];
            }
            // Show if has subscription but no capability access for this product type
            elseif ($hasSubscription && !$hasCapabilityAccess) {
                $showCapabilityBanner = true;
                $bannerInfo = [
                    'type' => 'upgrade',
                    'title' => "Upgrade your subscription",
                    'description' => "Your current subscription tier doesn't include this resource type. Upgrade to access this content."
                ];
            }
        }

        // Get the resources
        $resources = $this->resources->map(function ($resource) {
            return [
                'id' => $resource->id,
                'title' => $resource->title,
                'description' => $resource->description,
                'resource_type' => $resource->resource_type,
                'resource_url' => $resource->resource_url,
                'file_path' => $resource->file_path,
                'created_at' => $resource->created_at,
                'updated_at' => $resource->updated_at,
            ];
        });

        // Determine if user can access the product
        $canAccessProduct = $isPurchase || $hasCapabilityAccess;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => $this->price,
            'category_id' => $this->category_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'media' => $media,
            'type' => $this->type,
            'price_type' => $this->price_type,
            'is_purchase' => $isPurchase,
            'free_with_subscription' => $this->free_with_subscription,
            'has_capability_access' => $hasCapabilityAccess,
            'remaining_usage' => $remainingUsage,
            'is_out_of_usage' => $isOutOfUsage,
            'can_access' => $canAccessProduct,
            'resources' => $resources,
            'show_capability_banner' => $showCapabilityBanner,
            'banner_info' => $bannerInfo,
            'capability_label' => $capabilityLabel,
        ];
    }
}
