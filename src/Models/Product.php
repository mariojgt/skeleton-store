<?php
namespace Skeleton\Store\Models;

use Skeleton\Store\Enums\PriceType;
use Skeleton\Store\Enums\ProductType;
use Skeleton\Store\Enums\CapabilityType;
use Illuminate\Database\Eloquent\SoftDeletes;
use Skeleton\Store\Services\CapabilityService;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends BaseMasterModel
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'price',
        'category_id',
        'free_with_subscription'
    ];

    protected $casts = [
        'type' => ProductType::class,
        'price_type' => PriceType::class,
        'free_with_subscription' => 'boolean',
    ];

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function resources()
    {
        return $this->hasMany(ProductResource::class);
    }

    /**
     * Get the capability type associated with this product
     */
    public function getCapabilityType(): ?string
    {
        if ($this->type === ProductType::DIGITAL_RESOURCE) {
            return CapabilityType::DIGITAL_RESOURCES->value;
        } elseif ($this->type === ProductType::PROJECT_TEMPLATE) {
            return CapabilityType::PROJECT_TEMPLATES->value;
        }

        return null;
    }

    /**
     * Check if a user has purchased this product
     */
    public function isPurchasedBy(User $user): bool
    {
        return $user->paidOrders()
            ->whereHas('orderItems', function ($query) {
                $query->where([
                    'item_id' => $this->id,
                    'item_type' => self::class
                ]);
            })
            ->exists();
    }

    /**
     * Check if a user can access this product via capability
     */
    public function canAccessViaCapability(User $user): bool
    {
        $capabilitySlug = $this->getCapabilityType();

        if (!$capabilitySlug) {
            return false;
        }

        try {
            $capabilityService = app(CapabilityService::class);
            return $capabilityService->userHasCapability($user, $capabilitySlug) &&
                   $capabilityService->userCanUseCapability($user, $capabilitySlug);
        } catch (\Exception $e) {
            // Fallback to traditional subscription check if capability service fails
            return $this->free_with_subscription && $user->activeSubscription();
        }
    }
}
