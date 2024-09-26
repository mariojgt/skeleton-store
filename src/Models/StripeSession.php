<?php

namespace Skeleton\Store\Models;

use Carbon\Carbon;
use App\Models\User;
use Skeleton\Store\Enums\PriceType;
use Skeleton\Store\Enums\ProductType;
use Skeleton\Store\Enums\SubscriptionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class StripeSession extends BaseMasterModel
{
    use HasFactory;

    protected $fillable = ['session_id', 'status', 'user_id'];
}
