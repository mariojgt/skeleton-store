<?php

namespace Skeleton\Store\Controllers\Backend\Settings;

use Inertia\Inertia;
use Skeleton\Store\Models\Plan;
use Skeleton\Store\Models\User;
use Skeleton\Store\Models\Product;
use App\Http\Controllers\Controller;
use Mariojgt\Builder\Enums\FieldTypes;
use Skeleton\Store\Enums\DurationType;
use Skeleton\Store\Models\StoreSetting;
use Mariojgt\Builder\Helpers\FormHelper;
use Skeleton\Store\Events\UserSubscribedToPlan;
use Mariojgt\SkeletonAdmin\Enums\PermissionEnum;

class StoreSettingsController extends Controller
{
    /**
     * @return [blade view]
     */
    public function index()
    {
        // $user = User::find(3);
        // $plan = Plan::find(2);
        // event(new UserSubscribedToPlan($user, $plan));
        // Build the breadcrumb
        $breadcrumb = [
            [
                'label' => 'Store Settings',
                'url'   => route('admin.store.plans.index'),
            ]
        ];

        // Initialize form helper
        $form = new FormHelper();
        $formConfig = $form
        // Add fields
        ->addIdField()
        ->addField(
            label: 'Key',
            key: 'key',
            sortable: true,
            canCreate: true,
            canEdit: true,
            type: FieldTypes::TEXT->value
        )
        ->addField(
            label: 'Value',
            key: 'value',
            sortable: true,
            canCreate: true,
            canEdit: true,
            type: FieldTypes::TEXT->value
        )
        // Set endpoints
        ->setEndpoints(
            listEndpoint: route('admin.api.generic.table'),
            deleteEndpoint: route('admin.api.generic.table.delete'),
            createEndpoint: route('admin.api.generic.table.create'),
            editEndpoint: route('admin.api.generic.table.update')
        )
        // Set model
        ->setModel(StoreSetting::class)
        // Set permissions
        ->setPermissions(
            guard: 'skeleton_admin',
            type: 'permission',
            permissions: [
                'store'  => PermissionEnum::CreatePermission->value,
                'update' => PermissionEnum::EditPermission->value,
                'delete' => PermissionEnum::DeletePermission->value,
                'index'  => PermissionEnum::ReadPermission->value,
            ]
        )
        ->build();

        return Inertia::render('BackEnd/Vendor/skeleton-store/settings/index', [
            'title'      => 'Settings',
            'table_name' => 'store_settings',
            'breadcrumb' => $breadcrumb,
            ...$formConfig
        ]);
    }
}
