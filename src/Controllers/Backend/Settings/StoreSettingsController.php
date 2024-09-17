<?php

namespace Skeleton\Store\Controllers\Backend\Settings;

use Inertia\Inertia;
use Skeleton\Store\Models\Plan;
use Skeleton\Store\Models\StoreSetting;
use Skeleton\Store\Models\User;
use Skeleton\Store\Models\Product;
use App\Http\Controllers\Controller;
use Mariojgt\Builder\Enums\FieldTypes;
use Skeleton\Store\Enums\DurationType;
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

        // Table columns
        $columns = [
            [
                'label'     => 'Id',    // Display name
                'key'       => 'id',    // Table column key
                'sortable'  => true,    // Can be use in the filter
                'canCreate' => false,   // Can be use in the create form
                'canEdit'   => false,   // Can be use in the edit form
            ],
            [
                'label'     => 'Key',   // Display name
                'key'       => 'key',   // Table column key
                'sortable'  => true,           // Can be use in the filter
                'canCreate' => true,          // Can be use in the create form
                'canEdit'   => true,           // Can be use in the edit form
                'type'      => FieldTypes::TEXT->value,         // Type text,email,password,date,timestamp
            ],
            [
                'label'     => 'Value',   // Display name
                'key'       => 'value',   // Table column key
                'sortable'  => true,           // Can be use in the filter
                'canCreate' => true,          // Can be use in the create form
                'canEdit'   => true,           // Can be use in the edit form
                'type'      => FieldTypes::TEXT->value,         // Type text,email,password,date,timestamp
            ],
        ];

        return Inertia::render('BackEnd/Vendor/skeleton-store/settings/index', [
            'title'      => 'Settings',
            'table_name' => 'store_settings',
            'breadcrumb' => $breadcrumb,
            // Required for the generic builder table api
            'endpoint'       => route('admin.api.generic.table'),
            'endpointDelete' => route('admin.api.generic.table.delete'),
            'endpointCreate' => route('admin.api.generic.table.create'),
            'endpointEdit'   => route('admin.api.generic.table.update'),
            // You table columns
            'columns'        => $columns,
            // The model where all those actions will take place
            'model'          => encrypt(StoreSetting::class),
            // If you want to protect your crud form you can use this below not required
            // The permission name for the crud
            'permission'     => encrypt([
                'guard'         => 'skeleton_admin',
                // You can use permission or role up to you
                'type'          => 'permission',
                // The permission name or role
                'key' => [
                    'store'  => PermissionEnum::CreatePermission->value,
                    'update' => PermissionEnum::EditPermission->value,
                    'delete' => PermissionEnum::DeletePermission->value,
                    'index'  => PermissionEnum::ReadPermission->value,
                ],
            ]),
        ]);
    }
}
