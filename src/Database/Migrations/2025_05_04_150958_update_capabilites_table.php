<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('plan_capabilities', function (Blueprint $table) {
            // Rename monthly_limit to usage_limit for more generic usage
            $table->renameColumn('monthly_limit', 'usage_limit');

            // Add restriction_type column
            $table->string('restriction_type')->default('monthly');

            // Add initial_credits column for credit-based capabilities
            $table->integer('initial_credits')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('plan_capabilities', function (Blueprint $table) {
            $table->renameColumn('usage_limit', 'monthly_limit');
            $table->dropColumn('restriction_type');
            $table->dropColumn('initial_credits');
        });
    }
};
