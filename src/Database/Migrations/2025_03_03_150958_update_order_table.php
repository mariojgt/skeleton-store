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
        Schema::table('orders', function (Blueprint $table) {
            // Rename stripe_session_id to payment_session_id if it exists
            if (Schema::hasColumn('orders', 'stripe_session_id')) {
                $table->renameColumn('stripe_session_id', 'payment_session_id');
            } else {
                $table->string('payment_session_id')->nullable();
            }

            // Add payment_gateway column if it doesn't exist
            if (!Schema::hasColumn('orders', 'payment_gateway')) {
                $table->string('payment_gateway')->default('stripe')->after('payment_session_id');
            }

            // Add other fields if they don't exist
            if (!Schema::hasColumn('orders', 'subtotal')) {
                $table->decimal('subtotal', 10, 2)->default(0)->after('total_amount');
            }

            if (!Schema::hasColumn('orders', 'tax')) {
                $table->decimal('tax', 10, 2)->default(0)->after('subtotal');
            }

            if (!Schema::hasColumn('orders', 'discount')) {
                $table->decimal('discount', 10, 2)->default(0)->after('tax');
            }

            if (!Schema::hasColumn('orders', 'invoice_id')) {
                $table->string('invoice_id')->nullable()->after('payment_gateway');
            }

            if (!Schema::hasColumn('orders', 'invoice_url')) {
                $table->string('invoice_url')->nullable()->after('invoice_id');
            }

            if (!Schema::hasColumn('orders', 'notes')) {
                $table->text('notes')->nullable()->after('invoice_url');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Only attempt to reverse changes if the columns exist
            if (Schema::hasColumn('orders', 'payment_session_id')) {
                $table->renameColumn('payment_session_id', 'stripe_session_id');
            }

            if (Schema::hasColumn('orders', 'payment_gateway')) {
                $table->dropColumn('payment_gateway');
            }

            if (Schema::hasColumn('orders', 'subtotal')) {
                $table->dropColumn('subtotal');
            }

            if (Schema::hasColumn('orders', 'tax')) {
                $table->dropColumn('tax');
            }

            if (Schema::hasColumn('orders', 'discount')) {
                $table->dropColumn('discount');
            }

            if (Schema::hasColumn('orders', 'invoice_id')) {
                $table->dropColumn('invoice_id');
            }

            if (Schema::hasColumn('orders', 'invoice_url')) {
                $table->dropColumn('invoice_url');
            }

            if (Schema::hasColumn('orders', 'notes')) {
                $table->dropColumn('notes');
            }
        });
    }
};
