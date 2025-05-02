<?php

namespace Skeleton\Store\Middleware;

use Closure;
use Illuminate\Http\Request;
use Skeleton\Store\Models\User;
use Skeleton\Store\Enums\CapabilityType;
use Skeleton\Store\Services\CapabilityService;

class CheckCapability
{
    protected $capabilityService;

    public function __construct(CapabilityService $capabilityService)
    {
        $this->capabilityService = $capabilityService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $capability
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $capability, ?int $amount = 1)
    {
        $user = $request->user();
        // cast the $user to Skeleton\Store\Models\User
        $user = User::find($user->id);

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'You must be logged in to access this page.');
        }

        // Validate capability
        try {
            $capabilityType = CapabilityType::from($capability);
        } catch (\ValueError $e) {
            // Invalid capability
            abort(500, 'Invalid capability configuration');
        }

        // Check if user has capability
        if (!$this->capabilityService->userHasCapability($user, $capabilityType->value)) {
            return redirect()->route('plans.index')
                ->with('error', 'You need a subscription to access ' . $capabilityType->label());
        }

        // Check if user can use capability
        if (!$this->capabilityService->userCanUseCapability($user, $capabilityType->value, $amount)) {
            $remaining = $this->capabilityService->getRemainingUses($user, $capabilityType->value);

            if ($remaining === 0) {
                return redirect()->back()
                    ->with('error', 'You have reached your limit for ' . $capabilityType->label());
            } else {
                return redirect()->back()
                    ->with('error', "You don't have enough remaining uses. Available: {$remaining}, Required: {$amount}");
            }
        }

        // Record usage before proceeding
        $this->capabilityService->recordCapabilityUsage($user, $capabilityType->value, $amount);

        return $next($request);
    }
}
