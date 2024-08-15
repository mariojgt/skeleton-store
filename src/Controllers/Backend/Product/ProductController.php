<?php

namespace Skeleton\Store\Controllers\Backend\Product;

use Inertia\Inertia;
use Skeleton\Store\Models\Product;
use Skeleton\Store\Models\Category;
use App\Http\Controllers\Controller;
use Mariojgt\Builder\Enums\FieldTypes;
use Mariojgt\SkeletonAdmin\Enums\PermissionEnum;

class ProductController extends Controller
{
    /**
     * @return [blade view]
     */
    public function index()
    {
        // Build the breadcrumb
        $breadcrumb = [
            [
                'label' => 'Product',
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
                'label'     => 'Name',                    // Display name
                'key'       => 'name',                    // Table column key
                'sortable'  => true,                      // Can be use in the filter
                'canCreate' => true,                      // Can be use in the create form
                'canEdit'   => true,                      // Can be use in the edit form
                'type'      => FieldTypes::TEXT->value,   // Type text,email,password,date,timestamp
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
                'label'     => 'Price',
                'key'       => 'price',
                'sortable'  => true,
                'canCreate' => true,
                'canEdit'   => true,
                'type'      => FieldTypes::NUMBER->value,
            ],
            [
                'label'     => 'Category',
                'key'       => 'category_id',
                'sortable'  => false,
                'canCreate' => true,
                'canEdit'   => true,
                'nullable'  => true,
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
                'model'        => encrypt(Category::class),
                'singleSearch' => true,
                'displayKey'   => 'name'
            ],
            [
                'label'     => 'Image',
                'key'       => 'image',
                'sortable'  => false,
                'canCreate' => true,
                'canEdit'   => true,
                'nullable'  => true,
                'type'      => 'media',
                'endpoint'  => route('admin.api.media.search'),
            ]
        ];

        return Inertia::render('Vendor/skeleton-store/product/index', [
            'title'      => 'Product | 📦',
            'table_name' => 'Product',
            'breadcrumb' => $breadcrumb,
            // Required for the generic builder table api
            'endpoint'       => route('admin.api.generic.table'),
            'endpointDelete' => route('admin.api.generic.table.delete'),
            'endpointCreate' => route('admin.api.generic.table.create'),
            'endpointEdit'   => route('admin.api.generic.table.update'),
            // You table columns
            'columns'        => $columns,
            // The model where all those actions will take place
            'model'          => encrypt(Product::class),
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
