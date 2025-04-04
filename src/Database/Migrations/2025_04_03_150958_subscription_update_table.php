<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            // Rename stripe_subscription_id to subscription_id if it exists
            if (Schema::hasColumn('subscriptions', 'stripe_subscription_id')) {
                $table->renameColumn('stripe_subscription_id', 'subscription_id');
            } else {
                $table->string('subscription_id')->nullable();
            }

            // Add payment_gateway column if it doesn't exist
            if (!Schema::hasColumn('subscriptions', 'payment_gateway')) {
                $table->string('payment_gateway')->default('stripe')->after('subscription_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            // Only attempt to reverse changes if the columns exist
            if (Schema::hasColumn('subscriptions', 'subscription_id')) {
                $table->renameColumn('subscription_id', 'stripe_subscription_id');
            }

            if (Schema::hasColumn('subscriptions', 'payment_gateway')) {
                $table->dropColumn('payment_gateway');
            }
        });
    }
};
