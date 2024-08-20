<?php

namespace Skeleton\Store\Controllers\Backend\ProductCategory;

use Inertia\Inertia;
use Skeleton\Store\Models\Category;
use App\Http\Controllers\Controller;
use Mariojgt\Builder\Enums\FieldTypes;
use Mariojgt\SkeletonAdmin\Enums\PermissionEnum;

class ProductCategoryController extends Controller
{
    /**
     * @return [blade view]
     */
    public function index()
    {
        // Build the breadcrumb
        $breadcrumb = [
            [
                'label' => 'Product category',
                'url'   => route('admin.store.product-category.index'),
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
                'label'     => 'Slug',
                'key'       => 'slug',
                'sortable'  => true,
                'unique'    => true,
                'canCreate' => true,
                'canEdit'   => true,
                'type'      => FieldTypes::SLUG->value,
            ],
            [
                'label'     => 'Svg',
                'key'       => 'svg',
                'sortable'  => true,
                'canCreate' => true,
                'canEdit'   => true,
                'type'      => FieldTypes::ICON->value,
            ],
        ];

        return Inertia::render('BackEnd/Vendor/skeleton-store/productCategory/index', [
            'title'      => 'Category | Index',
            'table_name' => 'Category',
            'breadcrumb' => $breadcrumb,
            // Required for the generic builder table api
            'endpoint'       => route('admin.api.generic.table'),
            'endpointDelete' => route('admin.api.generic.table.delete'),
            'endpointCreate' => route('admin.api.generic.table.create'),
            'endpointEdit'   => route('admin.api.generic.table.update'),
            // You table columns
            'columns'        => $columns,
            // The model where all those actions will take place
            'model'          => encrypt(Category::class),
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
