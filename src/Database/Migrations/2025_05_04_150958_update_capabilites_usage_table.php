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
        Schema::table('capability_usages', function (Blueprint $table) {
            // Rename reset_date to last_reset for clarity
            $table->renameColumn('reset_date', 'last_reset');

            // Add a next_reset column for scheduling the next reset
            $table->timestamp('next_reset')->nullable();

            // Add a remaining_credits column for credit-based capabilities
            $table->integer('remaining_credits')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('capability_usages', function (Blueprint $table) {
            $table->renameColumn('last_reset', 'reset_date');
            $table->dropColumn('next_reset');
            $table->dropColumn('remaining_credits');
        });
    }
};
