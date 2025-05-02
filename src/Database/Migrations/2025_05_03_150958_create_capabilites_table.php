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
        // Create capabilities table
        Schema::create('capabilities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Create plan_capabilities table
        Schema::create('plan_capabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained();
            $table->foreignId('capability_id')->constrained();
            $table->integer('monthly_limit')->default(0);
            $table->boolean('is_unlimited')->default(false);
            $table->timestamps();

            $table->unique(['plan_id', 'capability_id']);
        });

        // Create capability_usage table
        Schema::create('capability_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('capability_id')->constrained();
            $table->foreignId('subscription_id')->constrained();
            $table->integer('usage_count')->default(0);
            $table->timestamp('reset_date');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'capability_id', 'subscription_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('capability_usages');
        Schema::dropIfExists('plan_capabilities');
        Schema::dropIfExists('capabilities');
    }
};
