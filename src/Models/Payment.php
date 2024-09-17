<?php

namespace Skeleton\Store\Models;

use App\Models\User;
use Skeleton\Store\Enums\PaymentMethod;
use Skeleton\Store\Enums\PriceType;
use Skeleton\Store\Enums\ProductType;
use Skeleton\Store\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends BaseMasterModel
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['user_id', 'subscription_id', 'total_amount', 'discount', 'tax', 'payment_method', 'status', 'transaction_id'];

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

    public function payable()
    {
        return $this->morphTo();
    }
}
