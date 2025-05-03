<?php

namespace Skeleton\Store\Controllers\Backend\Subscriptions;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Skeleton\Store\Models\Plan;
use Skeleton\Store\Models\Subscription;
use Skeleton\Store\Models\User;
use Skeleton\Store\Enums\SubscriptionStatus;
use Skeleton\Store\Events\UserSubscribedToPlan;
use Skeleton\Store\Events\UserUnsubscribedToPlan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SubscriptionController extends Controller
{
    /**
     * Display subscription management dashboard
     */
    public function index()
    {
        // Get subscription stats
        $stats = $this->getSubscriptionStats();

        // Get latest 10 subscriptions
        $subscriptions = Subscription::with(['user', 'plan'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Get subscription statuses for filter
        $statuses = SubscriptionStatus::array();

        // Get all plans for filter and creation
        $plans = Plan::where('is_active', true)
            ->orderBy('name')
            ->get();

        return Inertia::render('BackEnd/Vendor/skeleton-store/subscriptions/index', [
            'stats' => $stats,
            'subscriptions' => $subscriptions,
            'statuses' => $statuses,
            'plans' => $plans
        ]);
    }

    /**
     * Get list of subscriptions with filters
     */
    public function list(Request $request)
    {
        $query = Subscription::with(['user', 'plan']);

        // Apply filters
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('plan_id') && $request->plan_id) {
            $query->where('plan_id', $request->plan_id);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->where('start_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->where('end_date', '<=', $request->date_to);
        }

        // Sort
        $sortField = $request->sort_by ?? 'created_at';
        $sortDirection = $request->sort_direction ?? 'desc';
        $query->orderBy($sortField, $sortDirection);

        // Paginate
        $subscriptions = $query->paginate(15);

        return response()->json($subscriptions);
    }

    /**
     * Store a new subscription
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'plan_id' => 'required|exists:plans,id',
            'start_date' => 'required|date',
            'auto_renew' => 'boolean',
        ]);

        $plan = Plan::findOrFail($request->plan_id);
        $user = User::findOrFail($request->user_id);

        // Calculate end date based on plan duration
        $startDate = Carbon::parse($request->start_date);
        $endDate = $this->calculateEndDate($startDate, $plan);

        $subId = 'manual-' . time();

        event(new UserSubscribedToPlan(
            $user,
            $plan,
            null,
            $request->auto_renew ?? false,
            $subId,
            'manual'
        ));

        $subscription = $user->activeSubscription();

        return response()->json([
            'message' => 'Subscription created successfully',
            'subscription' => $subscription->load(['user', 'plan'])
        ]);
    }

    /**
     * Get a specific subscription
     */
    public function show($id)
    {
        $subscription = Subscription::with(['user', 'plan', 'payments'])->findOrFail($id);

        return Inertia::render('BackEnd/Vendor/skeleton-store/subscriptions/edit', [
            'subscription' => $subscription,
            'duration_left' => $subscription->durationLeft(),
            'statuses' => SubscriptionStatus::array(),
            'plans' => Plan::where('is_active', true)->orderBy('name')->get()
        ]);
    }

    /**
     * Update subscription status
     */
    public function updateStatus(Request $request, $id)
    {
        $subscription = Subscription::findOrFail($id);

        $request->validate([
            'status' => 'required|string|in:' . implode(',', array_keys(SubscriptionStatus::array())),
        ]);

        $oldStatus = $subscription->status;
        $subscription->status = $request->status;
        $subscription->save();

        // Trigger events if needed
        if ($oldStatus !== SubscriptionStatus::canceled && $request->status === SubscriptionStatus::canceled->value) {
            event(new UserUnsubscribedToPlan($subscription->user, $subscription->plan));
        }

        return response()->json([
            'message' => 'Subscription status updated successfully',
            'subscription' => $subscription->load(['user', 'plan'])
        ]);
    }

    /**
     * Change subscription plan
     */
    public function changePlan(Request $request, $id)
    {
        $subscription = Subscription::findOrFail($id);

        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'adjust_end_date' => 'boolean',
        ]);

        $newPlan = Plan::findOrFail($request->plan_id);
        $oldPlan = $subscription->plan;

        // Update plan
        $subscription->plan_id = $newPlan->id;

        // Adjust end date if requested
        if ($request->adjust_end_date) {
            $subscription->end_date = $this->calculateEndDate(
                Carbon::parse($subscription->start_date),
                $newPlan
            );
        }

        $subscription->save();

        $user = User::find($subscription->user_id);
        // Trigger events
        event(new UserUnsubscribedToPlan($user, $oldPlan));
        event(new UserSubscribedToPlan(
            $user,
            $newPlan,
            null,
            $subscription->auto_renew,
            $subscription->subscription_id,
            'manual'
        ));

        return response()->json([
            'message' => 'Subscription plan changed successfully',
            'subscription' => $subscription->load(['user', 'plan'])
        ]);
    }

    /**
     * Extend subscription
     */
    public function extend(Request $request, $id)
    {
        $subscription = Subscription::findOrFail($id);

        $request->validate([
            'duration' => 'required|integer|min:1',
            'duration_type' => 'required|string|in:days,weeks,months,years',
        ]);

        $currentEndDate = Carbon::parse($subscription->end_date);

        // Add time based on duration type
        switch ($request->duration_type) {
            case 'days':
                $subscription->end_date = $currentEndDate->addDays($request->duration);
                break;
            case 'weeks':
                $subscription->end_date = $currentEndDate->addWeeks($request->duration);
                break;
            case 'months':
                $subscription->end_date = $currentEndDate->addMonths($request->duration);
                break;
            case 'years':
                $subscription->end_date = $currentEndDate->addYears($request->duration);
                break;
        }

        $subscription->save();

        return response()->json([
            'message' => 'Subscription extended successfully',
            'subscription' => $subscription->load(['user', 'plan'])
        ]);
    }

    /**
     * Toggle auto-renew setting
     */
    public function toggleAutoRenew($id)
    {
        $subscription = Subscription::findOrFail($id);
        $subscription->auto_renew = !$subscription->auto_renew;
        $subscription->save();

        return response()->json([
            'message' => 'Auto-renew setting updated',
            'subscription' => $subscription
        ]);
    }

    /**
     * Cancel subscription
     */
    public function cancel($id)
    {
        $subscription = Subscription::findOrFail($id);

        if ($subscription->status !== SubscriptionStatus::canceled) {
            $subscription->status = SubscriptionStatus::canceled;
            $subscription->save();

            $user = User::find($subscription->user_id);
            event(new UserUnsubscribedToPlan($user, $subscription->plan));
        }

        return response()->json([
            'message' => 'Subscription cancelled successfully',
            'subscription' => $subscription->load(['user', 'plan'])
        ]);
    }

    /**
     * Search users for subscription creation
     */
    public function searchUsers(Request $request)
    {
        $search = $request->search;

        $users = User::where('first_name', 'like', "%{$search}%")
            ->orWhere('email', 'like', "%{$search}%")
            ->take(10)
            ->get(['id', 'first_name', 'email']);

        return response()->json($users);
    }

    /**
     * Get subscription statistics
     */
    private function getSubscriptionStats()
    {
        // Total active subscriptions
        $activeCount = Subscription::where('status', SubscriptionStatus::active)->count();

        // Total revenue (sum of plan prices)
        $totalRevenue = Subscription::join('plans', 'plans.id', '=', 'subscriptions.plan_id')
            ->where('subscriptions.status', SubscriptionStatus::active)
            ->sum('plans.price');

        // Subscriptions by status - FIX: Use status as a string value, not enum
        $byStatus = Subscription::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->get()
            ->mapWithKeys(function ($item) {
                // Convert enum to string value for the key
                $statusValue = $item->status instanceof SubscriptionStatus
                    ? $item->status->value
                    : (string) $item->status;

                return [$statusValue => $item->count];
            })
            ->toArray();

        // Subscriptions by plan
        $byPlan = Subscription::join('plans', 'plans.id', '=', 'subscriptions.plan_id')
            ->select('plans.name', DB::raw('count(*) as count'))
            ->where('subscriptions.status', SubscriptionStatus::active)
            ->groupBy('plans.name')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->name => $item->count];
            })
            ->toArray();

        // Expiring soon (within 7 days)
        $expiringSoon = Subscription::where('status', SubscriptionStatus::active)
            ->where('end_date', '<=', Carbon::now()->addDays(7))
            ->count();

        return [
            'active_count' => $activeCount,
            'total_revenue' => $totalRevenue,
            'by_status' => $byStatus,
            'by_plan' => $byPlan,
            'expiring_soon' => $expiringSoon
        ];
    }

    /**
     * Calculate end date based on plan duration
     */
    private function calculateEndDate(Carbon $startDate, Plan $plan)
    {
        $duration = $plan->duration;
        $durationType = $plan->duration_type;

        switch ($durationType->value) {
            case 'days':
                return $startDate->copy()->addDays($duration);
            case 'weeks':
                return $startDate->copy()->addWeeks($duration);
            case 'months':
                return $startDate->copy()->addMonths($duration);
            case 'years':
                return $startDate->copy()->addYears($duration);
            default:
                return $startDate->copy()->addDays($duration);
        }
    }
}
