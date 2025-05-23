<?php

namespace Skeleton\Store\Controllers\Backend\ProductCategory;

use Inertia\Inertia;
use Skeleton\Store\Models\Category; // This is the Category model for products
use App\Http\Controllers\Controller;
use Mariojgt\Builder\Enums\FieldTypes;
use Mariojgt\Builder\Helpers\FormHelper;
use Mariojgt\SkeletonAdmin\Enums\PermissionEnum;
use Mariojgt\SkeletonAdmin\Controllers\Backend\Web\Crud\GenericCrudController; // Added this use statement

class ProductCategoryController extends GenericCrudController // Changed to extend GenericCrudController
{
    public function __construct()
    {
        $this->title = 'Category | Index'; // Title for the generic admin page
        $this->model = Category::class;
    }

    protected function getFormConfig(): FormHelper
    {
        return (new FormHelper())
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
            ->addIconField( // Using the dedicated addIconField helper
                label: 'Svg',
                key: 'svg',
                sortable: true,
                canCreate: true,
                canEdit: true,
            );
    }
}
