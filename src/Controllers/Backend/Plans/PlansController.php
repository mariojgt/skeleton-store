<?php

namespace Skeleton\Store\Controllers\Backend\Plans;

use Inertia\Inertia;
use Skeleton\Store\Models\Plan;
use Skeleton\Store\Models\User;
use Skeleton\Store\Models\Product;
use App\Http\Controllers\Controller;
use Mariojgt\Builder\Enums\FieldTypes;
use Skeleton\Store\Enums\DurationType;
use Skeleton\Store\Events\UserSubscribedToPlan;
use Mariojgt\SkeletonAdmin\Enums\PermissionEnum;

class PlansController extends Controller
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
                'label' => 'Plans',
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
                'label'     => 'Name',   // Display name
                'key'       => 'name',   // Table column key
                'sortable'  => true,           // Can be use in the filter
                'canCreate' => true,          // Can be use in the create form
                'canEdit'   => true,           // Can be use in the edit form
                'type'      => FieldTypes::TEXT->value,         // Type text,email,password,date,timestamp
            ],
            [
                'label'     => 'Description',
                'key'       => 'description',
                'sortable'  => false,
                'unique'    => false,
                'canCreate' => true,
                'canEdit'   => true,
                'type'      => FieldTypes::TEXT->value,
            ],
            [
                'label'     => 'Price',
                'key'       => 'price',
                'sortable'  => true,
                'canCreate' => true,
                'canEdit'   => true,
                'type'      => FieldTypes::NUMBER->value,
            ],
            [
                'label'     => 'Duration',
                'key'       => 'duration',
                'sortable'  => true,
                'canCreate' => true,
                'canEdit'   => true,
                'type'      => FieldTypes::NUMBER->value,
            ],
            [
                'label'          => 'Duration Type',
                'key'            => 'duration_type',
                'sortable'       => true,
                'canCreate'      => true,
                'canEdit'        => true,
                'type'      => FieldTypes::SELECT->value,
                'select_options' => DurationType::array(),
            ],
            [
                'label'     => 'Is Active',
                'key'       => 'is_active',
                'sortable'  => true,
                'canCreate' => true,
                'canEdit'   => true,
                'type'      => FieldTypes::BOOLEAN->value,
            ],
            [
                'label'     => 'Product',
                'key'       => 'product_id',
                'sortable'  => false,
                'canCreate' => true,
                'canEdit'   => true,
                'nullable'  => false,
                'type'      => 'model_search',
                'endpoint'  => route('admin.api.generic.table'),
                'columns' => [
                    [
                        'key'       => 'id',
                        'sortable'  => false
                    ],
                    [
                        'key'       => 'name',
                        'sortable'  => true,
                    ],
                ],
                'model'        => encrypt(Product::class),
                'singleSearch' => true,
                'displayKey'   => 'name'
            ],
        ];

        return Inertia::render('BackEnd/Vendor/skeleton-store/plans/index', [
            'title'      => 'Plans',
            'table_name' => 'Plans',
            'breadcrumb' => $breadcrumb,
            // Required for the generic builder table api
            'endpoint'       => route('admin.api.generic.table'),
            'endpointDelete' => route('admin.api.generic.table.delete'),
            'endpointCreate' => route('admin.api.generic.table.create'),
            'endpointEdit'   => route('admin.api.generic.table.update'),
            // You table columns
            'columns'        => $columns,
            // The model where all those actions will take place
            'model'          => encrypt(Plan::class),
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
