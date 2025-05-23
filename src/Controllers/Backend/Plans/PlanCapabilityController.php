<?php

namespace Skeleton\Store\Controllers\Backend\Plans;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Skeleton\Store\Models\Plan;
use Skeleton\Store\Models\User;
use Skeleton\Store\Models\Product;
use App\Http\Controllers\Controller;
use Skeleton\Store\Models\Capability;
use Mariojgt\Builder\Enums\FieldTypes;
use Skeleton\Store\Enums\DurationType;
use Mariojgt\Builder\Helpers\FormHelper;
use Mariojgt\SkeletonAdmin\Enums\PermissionEnum;

class PlanCapabilityController extends Controller
{
    public function editCapabilities($planId)
    {
        $plan = Plan::with(['capabilities'])->findOrFail($planId);
        $capabilities = Capability::all(); // All possible capabilities

        return Inertia::render('BackEnd/Vendor/skeleton-store/plans/editCapabilities', [
            'plan'         => $plan,
            'capabilities' => $capabilities,
        ]);
    }

    public function updateCapabilities(Request $request, $planId)
    {
        $plan = Plan::findOrFail($planId);

        $data = collect($request->input('capabilities'));

        $syncData = $data->mapWithKeys(function ($cap) {
            return [
                $cap['id'] => [
                    'usage_limit'     => $cap['usage_limit'],
                    'is_unlimited'    => $cap['is_unlimited'],
                    'restriction_type'=> $cap['restriction_type'] ?? 'default',
                    'initial_credits' => $cap['initial_credits'] ?? 0,
                ]
            ];
        })->toArray();

        $plan->capabilities()->sync($syncData);

        return redirect()->route('admin.store.plans.index')->with('success', 'Capabilities updated.');
    }

    public function store(Request $request, Plan $plan)
    {
        $request->validate([
            'capability_id' => 'required|exists:capabilities,id',
        ]);

        $plan->capabilities()->syncWithoutDetaching($request->capability_id);

        return back()->with('success', 'Capability added.');
    }

    public function destroy(Plan $plan, Capability $capability)
    {
        $plan->capabilities()->detach($capability->id);

        return back()->with('success', 'Capability removed.');
    }
}
