<?php

use Illuminate\Support\Facades\Schema;
use Skeleton\Store\Enums\PaymentMethod;
use Skeleton\Store\Enums\PaymentStatus;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::create('stripe_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->unique();
            $table->string('status')->default('pending'); // pending, completed, failed, etc.
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('stripe_sessions');
    }
};
