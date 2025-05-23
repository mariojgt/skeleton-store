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
use Mariojgt\SkeletonAdmin\Controllers\Backend\Web\Crud\GenericCrudController;

class PlansController extends GenericCrudController
{
    public function __construct()
    {
        $this->title = 'Plans | Index';
        $this->model = Plan::class;
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
            ->setCustomPointRoute(
                route: '/admin/capabilities/',
                customActionName: 'Capabilities',
            );
    }
}
