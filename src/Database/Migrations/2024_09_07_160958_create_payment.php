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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('subscription_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 8, 2);
            $table->string('payment_method')->default(PaymentMethod::stripe->value);
            $table->string('status')->default(PaymentStatus::processing->value);
            $table->string('transaction_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
};
