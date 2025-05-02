<?php

use Skeleton\Store\Models\Plan;
use Skeleton\Store\Models\Capability;
use Illuminate\Support\Facades\Schema;
use Skeleton\Store\Enums\DurationType;
use Skeleton\Store\Enums\CapabilityType;
use Illuminate\Database\Schema\Blueprint;
use Skeleton\Store\Enums\RestrictionType;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // make so the product_id is not required on the plans table
        Schema::table('plans', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->nullable()->change();
        });
        // all all the other plan inactive
        Plan::where('is_active', true)->update(['is_active' => false]);
        // Seed the capabilities first based on the enum
        $this->seedCapabilities();

        // Create all plans
        $this->createMonthlyPlans();
        $this->createYearlyPlans();
    }

    /**
     * Seed capabilities based on enum
     */
    private function seedCapabilities()
    {
        foreach (CapabilityType::cases() as $type) {
            Capability::updateOrCreate(
                ['slug' => $type->value],
                [
                    'name' => $type->label(),
                    'description' => $type->description(),
                    'is_active' => true,
                ]
            );
        }
    }

    /**
     * Create monthly subscription plans
     */
    private function createMonthlyPlans()
    {
        // Basic Monthly Plan
        $basicMonthly = Plan::create([
            'name' => 'Basic Monthly',
            'description' => 'Essential features with unlimited courses and limited downloads',
            'price' => 9.99,
            'duration' => 1,
            'duration_type' => DurationType::months,
            'is_active' => true,
            'auto_renew' => true,
        ]);

        // Attach capabilities to Basic Monthly Plan
        $basicMonthly->capabilities()->attach([
            // All plans have unlimited access to videos
            Capability::where('slug', CapabilityType::PREMIUM_COURSES->value)->first()->id => [
                'usage_limit' => 0,
                'is_unlimited' => true,
                'restriction_type' => RestrictionType::MONTHLY->value,
                'initial_credits' => 0,
            ],
            // Limited downloads
            Capability::where('slug', CapabilityType::DIGITAL_RESOURCES->value)->first()->id => [
                'usage_limit' => 3,
                'is_unlimited' => false,
                'restriction_type' => RestrictionType::MONTHLY->value,
                'initial_credits' => 0,
            ],
            // No project templates on basic plan
        ]);

        // Standard Monthly Plan
        $standardMonthly = Plan::create([
            'name' => 'Standard Monthly',
            'description' => 'More downloads and templates with unlimited courses',
            'price' => 19.99,
            'duration' => 1,
            'duration_type' => DurationType::months,
            'is_active' => true,
            'auto_renew' => true,
        ]);

        // Attach capabilities to Standard Monthly Plan
        $standardMonthly->capabilities()->attach([
            // Unlimited video courses
            Capability::where('slug', CapabilityType::PREMIUM_COURSES->value)->first()->id => [
                'usage_limit' => 0,
                'is_unlimited' => true,
                'restriction_type' => RestrictionType::MONTHLY->value,
                'initial_credits' => 0,
            ],
            // More downloads
            Capability::where('slug', CapabilityType::DIGITAL_RESOURCES->value)->first()->id => [
                'usage_limit' => 10,
                'is_unlimited' => false,
                'restriction_type' => RestrictionType::MONTHLY->value,
                'initial_credits' => 0,
            ],
            // Limited project templates
            Capability::where('slug', CapabilityType::PROJECT_TEMPLATES->value)->first()->id => [
                'usage_limit' => 1,
                'is_unlimited' => false,
                'restriction_type' => RestrictionType::MONTHLY->value,
                'initial_credits' => 0,
            ],
        ]);

        // Premium Monthly Plan
        $premiumMonthly = Plan::create([
            'name' => 'Premium Monthly',
            'description' => 'Unlimited courses with generous download and template limits',
            'price' => 29.99,
            'duration' => 1,
            'duration_type' => DurationType::months,
            'is_active' => true,
            'auto_renew' => true,
        ]);

        // Attach capabilities to Premium Monthly Plan
        $premiumMonthly->capabilities()->attach([
            // Unlimited video courses
            Capability::where('slug', CapabilityType::PREMIUM_COURSES->value)->first()->id => [
                'usage_limit' => 0,
                'is_unlimited' => true,
                'restriction_type' => RestrictionType::MONTHLY->value,
                'initial_credits' => 0,
            ],
            // Large number of downloads
            Capability::where('slug', CapabilityType::DIGITAL_RESOURCES->value)->first()->id => [
                'usage_limit' => 25,
                'is_unlimited' => false,
                'restriction_type' => RestrictionType::MONTHLY->value,
                'initial_credits' => 0,
            ],
            // More generous template limit
            Capability::where('slug', CapabilityType::PROJECT_TEMPLATES->value)->first()->id => [
                'usage_limit' => 5,
                'is_unlimited' => false,
                'restriction_type' => RestrictionType::MONTHLY->value,
                'initial_credits' => 0,
            ],
        ]);
    }

    /**
     * Create yearly subscription plans
     */
    private function createYearlyPlans()
    {
        // Basic Yearly Plan (save 15%)
        $basicYearly = Plan::create([
            'name' => 'Basic Yearly',
            'description' => 'Essential features with unlimited courses and limited downloads - save 15%',
            'price' => 101.89, // 9.99 * 12 * 0.85 (15% discount)
            'duration' => 1,
            'duration_type' => DurationType::years,
            'is_active' => true,
            'auto_renew' => true,
        ]);

        // Attach capabilities to Basic Yearly Plan
        $basicYearly->capabilities()->attach([
            // Unlimited video courses
            Capability::where('slug', CapabilityType::PREMIUM_COURSES->value)->first()->id => [
                'usage_limit' => 0,
                'is_unlimited' => true,
                'restriction_type' => RestrictionType::YEARLY->value,
                'initial_credits' => 0,
            ],
            // Limited downloads but more than monthly × 12
            Capability::where('slug', CapabilityType::DIGITAL_RESOURCES->value)->first()->id => [
                'usage_limit' => 40, // More than monthly × 12
                'is_unlimited' => false,
                'restriction_type' => RestrictionType::YEARLY->value,
                'initial_credits' => 0,
            ],
            // No project templates on basic plan
        ]);

        // Standard Yearly Plan (save 15%)
        $standardYearly = Plan::create([
            'name' => 'Standard Yearly',
            'description' => 'More downloads and templates with unlimited courses - save 15%',
            'price' => 203.89, // 19.99 * 12 * 0.85 (15% discount)
            'duration' => 1,
            'duration_type' => DurationType::years,
            'is_active' => true,
            'auto_renew' => true,
        ]);

        // Attach capabilities to Standard Yearly Plan
        $standardYearly->capabilities()->attach([
            // Unlimited video courses
            Capability::where('slug', CapabilityType::PREMIUM_COURSES->value)->first()->id => [
                'usage_limit' => 0,
                'is_unlimited' => true,
                'restriction_type' => RestrictionType::YEARLY->value,
                'initial_credits' => 0,
            ],
            // More downloads
            Capability::where('slug', CapabilityType::DIGITAL_RESOURCES->value)->first()->id => [
                'usage_limit' => 130, // More than monthly × 12
                'is_unlimited' => false,
                'restriction_type' => RestrictionType::YEARLY->value,
                'initial_credits' => 0,
            ],
            // Limited project templates
            Capability::where('slug', CapabilityType::PROJECT_TEMPLATES->value)->first()->id => [
                'usage_limit' => 15, // More than monthly × 12
                'is_unlimited' => false,
                'restriction_type' => RestrictionType::YEARLY->value,
                'initial_credits' => 0,
            ],
        ]);

        // Premium Yearly Plan (save 15%)
        $premiumYearly = Plan::create([
            'name' => 'Premium Yearly',
            'description' => 'Unlimited courses with very generous download and template limits - save 15%',
            'price' => 305.89, // 29.99 * 12 * 0.85 (15% discount)
            'duration' => 1,
            'duration_type' => DurationType::years,
            'is_active' => true,
            'auto_renew' => true,
        ]);

        // Attach capabilities to Premium Yearly Plan
        $premiumYearly->capabilities()->attach([
            // Unlimited video courses
            Capability::where('slug', CapabilityType::PREMIUM_COURSES->value)->first()->id => [
                'usage_limit' => 0,
                'is_unlimited' => true,
                'restriction_type' => RestrictionType::YEARLY->value,
                'initial_credits' => 0,
            ],
            // Very generous downloads but not unlimited
            Capability::where('slug', CapabilityType::DIGITAL_RESOURCES->value)->first()->id => [
                'usage_limit' => 350, // More than monthly × 12
                'is_unlimited' => false,
                'restriction_type' => RestrictionType::YEARLY->value,
                'initial_credits' => 0,
            ],
            // Very generous template limit
            Capability::where('slug', CapabilityType::PROJECT_TEMPLATES->value)->first()->id => [
                'usage_limit' => 20, // More than monthly × 12
                'is_unlimited' => false,
                'restriction_type' => RestrictionType::YEARLY->value,
                'initial_credits' => 0,
            ],
        ]);

        // Create Business Yearly Plan
        $businessYearly = Plan::create([
            'name' => 'Business Yearly',
            'description' => 'Perfect for teams and businesses - unlimited everything',
            'price' => 499.99,
            'duration' => 1,
            'duration_type' => DurationType::years,
            'is_active' => true,
            'auto_renew' => true,
        ]);

        // Attach capabilities to Business Yearly Plan - everything unlimited
        $businessYearly->capabilities()->attach([
            // Unlimited video courses
            Capability::where('slug', CapabilityType::PREMIUM_COURSES->value)->first()->id => [
                'usage_limit' => 0,
                'is_unlimited' => true,
                'restriction_type' => RestrictionType::YEARLY->value,
                'initial_credits' => 0,
            ],
            // Unlimited downloads
            Capability::where('slug', CapabilityType::DIGITAL_RESOURCES->value)->first()->id => [
                'usage_limit' => 0,
                'is_unlimited' => true,
                'restriction_type' => RestrictionType::YEARLY->value,
                'initial_credits' => 0,
            ],
            // Unlimited project templates
            Capability::where('slug', CapabilityType::PROJECT_TEMPLATES->value)->first()->id => [
                'usage_limit' => 0,
                'is_unlimited' => true,
                'restriction_type' => RestrictionType::YEARLY->value,
                'initial_credits' => 0,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Delete all plans created in this migration
        $planNames = [
            'Basic Monthly', 'Standard Monthly', 'Premium Monthly',
            'Basic Yearly', 'Standard Yearly', 'Premium Yearly', 'Business Yearly'
        ];

        $plans = Plan::whereIn('name', $planNames)->get();

        foreach ($plans as $plan) {
            // First detach all capabilities
            $plan->capabilities()->detach();
            // Then delete the plan
            $plan->delete();
        }

        // We don't delete the capabilities as they might be used by other plans
    }
};
