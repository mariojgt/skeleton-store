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
        // Update plans table to be payment gateway agnostic
        Schema::table('plans', function (Blueprint $table) {
            // Rename stripe_price_id to gateway_price_id
            if (Schema::hasColumn('plans', 'stripe_price_id')) {
                $table->renameColumn('stripe_price_id', 'gateway_price_id');
            } else {
                $table->string('gateway_price_id')->nullable();
            }

            // Add a column to track which payment gateway was used
            $table->string('payment_gateway')->default('stripe')->after('gateway_price_id');
        });

        // Update products table (if it exists and has similar columns)
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (Schema::hasColumn('products', 'stripe_price_id')) {
                    $table->renameColumn('stripe_price_id', 'gateway_price_id');
                } else {
                    $table->string('gateway_price_id')->nullable();
                }

                // Add a column to track which payment gateway was used
                $table->string('payment_gateway')->default('stripe')->after('gateway_price_id');
            });
        }

        // Update courses table (if it exists and has similar columns)
        if (Schema::hasTable('courses')) {
            Schema::table('courses', function (Blueprint $table) {
                if (Schema::hasColumn('courses', 'stripe_price_id')) {
                    $table->renameColumn('stripe_price_id', 'gateway_price_id');
                } else {
                    $table->string('gateway_price_id')->nullable();
                }

                // Add a column to track which payment gateway was used
                $table->string('payment_gateway')->default('stripe')->after('gateway_price_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse plans table changes
        Schema::table('plans', function (Blueprint $table) {
            if (Schema::hasColumn('plans', 'gateway_price_id')) {
                $table->renameColumn('gateway_price_id', 'stripe_price_id');
            }

            if (Schema::hasColumn('plans', 'payment_gateway')) {
                $table->dropColumn('payment_gateway');
            }
        });

        // Reverse products table changes
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (Schema::hasColumn('products', 'gateway_price_id')) {
                    $table->renameColumn('gateway_price_id', 'stripe_price_id');
                }

                if (Schema::hasColumn('products', 'payment_gateway')) {
                    $table->dropColumn('payment_gateway');
                }
            });
        }

        // Reverse courses table changes
        if (Schema::hasTable('courses')) {
            Schema::table('courses', function (Blueprint $table) {
                if (Schema::hasColumn('courses', 'gateway_price_id')) {
                    $table->renameColumn('gateway_price_id', 'stripe_price_id');
                }

                if (Schema::hasColumn('courses', 'payment_gateway')) {
                    $table->dropColumn('payment_gateway');
                }
            });
        }
    }
};
