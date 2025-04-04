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
        // Create new payment_sessions table
        Schema::create('payment_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->unique();
            $table->string('status')->default('pending');
            $table->foreignId('user_id')->constrained();
            $table->string('payment_gateway')->default('stripe');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        // Migrate data from stripe_sessions if it exists
        if (Schema::hasTable('stripe_sessions')) {
            DB::statement("
                INSERT INTO payment_sessions (session_id, status, user_id, payment_gateway, created_at, updated_at)
                SELECT session_id, status, user_id, 'stripe', created_at, updated_at
                FROM stripe_sessions
            ");
            // Drop the old stripe_sessions table
            Schema::dropIfExists('stripe_sessions');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_sessions');
    }
};
