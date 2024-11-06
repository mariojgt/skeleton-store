<?php

namespace Skeleton\Store\Controllers\Backend\ProductCategory;

use Inertia\Inertia;
use Skeleton\Store\Models\Category;
use App\Http\Controllers\Controller;
use Mariojgt\Builder\Enums\FieldTypes;
use Mariojgt\Builder\Helpers\FormHelper;
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
            label: 'Slug',
            key: 'slug',
            sortable: true,
            canCreate: true,
            canEdit: true,
            unique: true,
            type: FieldTypes::SLUG->value,
        )
        ->addIconField(
            label: 'Svg',
            key: 'svg',
            sortable: true,
            canCreate: true,
            canEdit: true,
        )
        // Set endpoints
        ->setEndpoints(
            listEndpoint: route('admin.api.generic.table'),
            deleteEndpoint: route('admin.api.generic.table.delete'),
            createEndpoint: route('admin.api.generic.table.create'),
            editEndpoint: route('admin.api.generic.table.update')
        )
        // Set model
        ->setModel(Category::class)
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

        return Inertia::render('BackEnd/Vendor/skeleton-store/productCategory/index', [
            'title'      => 'Category | Index',
            'table_name' => 'Category',
            'breadcrumb' => $breadcrumb,
            ...$formConfig
        ]);
    }
}
