<?php

namespace Skeleton\Store\Controllers\Backend\Settings;

use Inertia\Inertia;
use Skeleton\Store\Models\StoreSetting; // The model this controller manages
use App\Http\Controllers\Controller;
use Mariojgt\Builder\Enums\FieldTypes;
use Mariojgt\Builder\Helpers\FormHelper;
use Mariojgt\SkeletonAdmin\Enums\PermissionEnum;
use Mariojgt\SkeletonAdmin\Controllers\Backend\Web\Crud\GenericCrudController; // Added this use statement

class StoreSettingsController extends GenericCrudController // Changed to extend GenericCrudController
{
    public function __construct()
    {
        $this->title = 'Settings'; // Title for the generic admin page
        $this->model = StoreSetting::class; // The model managed by this controller
        // Set permissions based on the original controller's configuration
        $this->permissions = [
            'store'  => PermissionEnum::CreatePermission->value,
            'update' => PermissionEnum::EditPermission->value,
            'delete' => PermissionEnum::DeletePermission->value,
            'index'  => PermissionEnum::ReadPermission->value,
        ];
    }

    protected function getFormConfig(): FormHelper
    {
        return (new FormHelper())
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
            );
    }
}
