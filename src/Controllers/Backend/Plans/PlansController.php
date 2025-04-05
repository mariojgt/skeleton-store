<?php

namespace Skeleton\Store\Controllers\Backend\Plans;

use Inertia\Inertia;
use Skeleton\Store\Models\Plan;
use Skeleton\Store\Models\User;
use Skeleton\Store\Models\Product;
use App\Http\Controllers\Controller;
use Mariojgt\Builder\Enums\FieldTypes;
use Skeleton\Store\Enums\DurationType;
use Mariojgt\Builder\Helpers\FormHelper;
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

        // Initialize form helper
        $form = new FormHelper();
        $formConfig = $form
            // Add fields
            ->addIdField()
            ->addField(
                label: 'Name',
                key: 'name',
                sortable: true,
                canCreate: true,
                canEdit: true,
                type: FieldTypes::TEXT->value
            )
            ->addField(
                label: 'Description',
                key: 'description',
                sortable: false,
                canCreate: true,
                canEdit: true,
                unique: false,
                type: FieldTypes::TEXT->value,
            )
            ->addField(
                label: 'Price',
                key: 'price',
                sortable: true,
                canCreate: true,
                canEdit: true,
                unique: false,
                type: FieldTypes::NUMBER->value
            )
            ->addField(
                label: 'Duration',
                key: 'duration',
                sortable: true,
                canCreate: true,
                canEdit: true,
                type: FieldTypes::NUMBER->value
            )
            ->addSelectWithOptions(
                label: 'Duration Type',
                key: 'duration_type',
                options: DurationType::array()
            )
            ->addBooleanField(
                label: 'Is Active',
                key: 'is_active'
            )
            ->addField(
                label: 'Product',
                key: 'product_id',
                sortable: false,
                canCreate: true,
                canEdit: true,
                nullable: true,
                type: FieldTypes::MODEL_SEARCH->value,
                endpoint: route('admin.api.generic.table'),
                columns: [
                    [
                        'key'       => 'id',
                        'sortable'  => false
                    ],
                    [
                        'key'       => 'name',
                        'sortable'  => true,
                    ],
                ],
                model: encrypt(Product::class),
                singleSearch: true,
                displayKey: 'name'
            )
            // Set endpoints
            ->setEndpoints(
                listEndpoint: route('admin.api.generic.table'),
                deleteEndpoint: route('admin.api.generic.table.delete'),
                createEndpoint: route('admin.api.generic.table.create'),
                editEndpoint: route('admin.api.generic.table.update')
            )
            // Set model
            ->setModel(Plan::class)
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

        return Inertia::render('BackEnd/Vendor/skeleton-store/plans/index', [
            'title'      => 'Plans',
            'table_name' => 'Plans',
            'breadcrumb' => $breadcrumb,
            // Required for the generic builder table api
            ...$formConfig
        ]);
    }
}
