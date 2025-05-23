<?php

namespace Skeleton\Store\Controllers\Backend\Capabilities;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Skeleton\Store\Models\Capability;
use Skeleton\Store\Models\Subscription;
use Skeleton\Store\Models\PlanCapability;
use Skeleton\Store\Models\CapabilityUsage;
use Skeleton\Store\Enums\RestrictionType;

class UserCapabilityController extends Controller
{
    /**
     * Get list of all capabilities
     */
    public function list()
    {
        $capabilities = Capability::where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json($capabilities);
    }

    /**
     * Get user capabilities for a specific subscription
     */
    public function getUserCapabilities(Subscription $subscription)
    {
        // Get user's capabilities through the subscription plan
        $userCapabilities = [];

        $planCapabilities = PlanCapability::where('plan_id', $subscription->plan_id)->get();

        foreach ($planCapabilities as $planCapability) {
            $capability = Capability::find($planCapability->capability_id);

            if (!$capability || !$capability->is_active) {
                continue;
            }

            // Get usage information
            $usage = CapabilityUsage::firstOrCreate(
                [
                    'user_id' => $subscription->user_id,
                    'capability_id' => $capability->id,
                    'subscription_id' => $subscription->id,
                ],
                [
                    'usage_count' => 0,
                    'last_reset' => now(),
                    'next_reset' => $planCapability->restriction_type->isTimeBased()
                        ? now()->addDays($planCapability->restriction_type->getDaysUntilReset())
                        : null,
                    'remaining_credits' => $planCapability->initial_credits,
                ]
            );

            // Check if needs reset
            $usage->checkAndResetIfNeeded();

            // Merge capability and usage data
            $userCapabilities[] = [
                'id' => $capability->id,
                'name' => $capability->name,
                'description' => $capability->description,
                'slug' => $capability->slug,
                'usage_count' => $usage->usage_count,
                'usage_limit' => $planCapability->usage_limit,
                'is_unlimited' => $planCapability->is_unlimited,
                'restriction_type' => $planCapability->restriction_type->value,
                'next_reset' => $usage->next_reset,
                'remaining_credits' => $usage->remaining_credits,
            ];
        }

        return response()->json($userCapabilities);
    }

    /**
     * Add capability to a user's subscription
     */
    public function addCapability(Request $request)
    {
        $request->validate([
            'subscription_id' => 'required|exists:subscriptions,id',
            'capability_id' => 'required|exists:capabilities,id',
            'restriction_type' => 'required|string',
            'usage_limit' => 'required_if:is_unlimited,false|integer|min:0',
            'is_unlimited' => 'boolean',
        ]);

        $subscription = Subscription::findOrFail($request->subscription_id);
        $capability = Capability::findOrFail($request->capability_id);

        // Check if plan already has this capability
        $existingPlanCapability = PlanCapability::where('plan_id', $subscription->plan_id)
            ->where('capability_id', $capability->id)
            ->first();

        if ($existingPlanCapability) {
            return response()->json([
                'message' => 'This capability is already assigned to this plan',
            ], 422);
        }

        // Create plan capability
        $planCapability = PlanCapability::create([
            'plan_id' => $subscription->plan_id,
            'capability_id' => $capability->id,
            'usage_limit' => $request->is_unlimited ? 0 : $request->usage_limit,
            'is_unlimited' => $request->is_unlimited,
            'restriction_type' => $request->restriction_type,
            'initial_credits' => $request->restriction_type === 'credits' ? $request->usage_limit : 0,
        ]);

        // Create usage record
        $usage = CapabilityUsage::create([
            'user_id' => $subscription->user_id,
            'capability_id' => $capability->id,
            'subscription_id' => $subscription->id,
            'usage_count' => 0,
            'last_reset' => now(),
            'next_reset' => $planCapability->restriction_type === 'daily' ? now()->addDay() :
                ($planCapability->restriction_type === 'weekly' ? now()->addWeek() :
                ($planCapability->restriction_type === 'monthly' ? now()->addMonth() :
                ($planCapability->restriction_type === 'yearly' ? now()->addYear() : null))),
            'remaining_credits' => $planCapability->restriction_type === 'credits'
                ? $planCapability->initial_credits
                : 0,
        ]);

        return response()->json([
            'message' => 'Capability added successfully',
            'plan_capability' => $planCapability,
            'usage' => $usage,
        ]);
    }

    /**
     * Update capability usage
     */
    public function updateCapability(Request $request, Capability $capability)
    {
        $request->validate([
            'subscription_id' => 'required|exists:subscriptions,id',
            'usage_count' => 'required|integer|min:0',
            'is_unlimited' => 'boolean',
        ]);

        $subscription = Subscription::findOrFail($request->subscription_id);

        // Find plan capability
        $planCapability = PlanCapability::where('plan_id', $subscription->plan_id)
            ->where('capability_id', $capability->id)
            ->first();

        if (!$planCapability) {
            return response()->json([
                'message' => 'Capability not found for this plan',
            ], 404);
        }

        // Update plan capability unlimited status if provided
        if ($request->has('is_unlimited')) {
            $planCapability->is_unlimited = $request->is_unlimited;
            $planCapability->save();
        }

        // Find usage record
        $usage = CapabilityUsage::where('user_id', $subscription->user_id)
            ->where('capability_id', $capability->id)
            ->where('subscription_id', $subscription->id)
            ->first();

        if (!$usage) {
            return response()->json([
                'message' => 'Usage record not found',
            ], 404);
        }

        // Update usage record
        if ($planCapability->restriction_type === 'credits') {
            $usage->remaining_credits = $request->usage_count;
        } else {
            $usage->usage_count = $request->usage_count;
        }

        $usage->save();

        return response()->json([
            'message' => 'Capability updated successfully',
            'plan_capability' => $planCapability,
            'usage' => $usage,
        ]);
    }

    /**
     * Remove capability from a user's subscription
     */
    public function removeCapability(Request $request, Capability $capability)
    {
        $request->validate([
            'subscription_id' => 'required|exists:subscriptions,id',
        ]);

        $subscription = Subscription::findOrFail($request->subscription_id);

        // Find plan capability
        $planCapability = PlanCapability::where('plan_id', $subscription->plan_id)
            ->where('capability_id', $capability->id)
            ->first();

        if (!$planCapability) {
            return response()->json([
                'message' => 'Capability not found for this plan',
            ], 404);
        }

        // Find usage record
        $usage = CapabilityUsage::where('user_id', $subscription->user_id)
            ->where('capability_id', $capability->id)
            ->where('subscription_id', $subscription->id)
            ->first();

        // Delete records
        if ($usage) {
            $usage->delete();
        }

        $planCapability->delete();

        return response()->json([
            'message' => 'Capability removed successfully',
        ]);
    }
}
