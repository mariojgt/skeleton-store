<?php

namespace Skeleton\Store\Models;

use App\Models\User;
use Skeleton\Store\Enums\PaymentMethod;
use Skeleton\Store\Enums\PriceType;
use Skeleton\Store\Enums\ProductType;
use Skeleton\Store\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends BaseMasterModel
{
    use HasFactory;

    protected $fillable = ['user_id', 'subscription_id', 'amount', 'payment_method', 'status', 'transaction_id'];

    protected $casts = [
        'status'         => PaymentStatus::class,
        'payment_method' => PaymentMethod::class
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
}
