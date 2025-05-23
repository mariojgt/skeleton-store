<?php

namespace Skeleton\Store\Controllers\Backend\Capabilities;

use Inertia\Inertia;
use App\Http\Controllers\Controller;
use Mariojgt\Builder\Enums\FieldTypes;
use Mariojgt\Builder\Helpers\FormHelper;
use Mariojgt\SkeletonAdmin\Enums\PermissionEnum;
use Mariojgt\SkeletonAdmin\Controllers\Backend\Web\Crud\GenericCrudController; // Use the GenericCrudController
use Skeleton\Store\Models\Capability;

class CapabilityController extends GenericCrudController
{
    public function __construct()
    {
        $this->title = 'Capabilities'; // Title for the admin page
        $this->model = Capability::class;
    }

    protected function getFormConfig(): FormHelper
    {
        return (new FormHelper())
            ->addIdField() // Add the ID field
            ->addField(
                label: 'Name',
                key: 'name',
                sortable: true,
                canCreate: true,
                canEdit: true,
                type: FieldTypes::TEXT->value // For 'name'
            )
            ->addField(
                label: 'Slug',
                key: 'slug',
                sortable: true,
                canCreate: true,
                canEdit: true,
                unique: true, // Slugs are typically unique
                type: FieldTypes::SLUG->value // For 'slug'
            )
            ->addField(
                label: 'Description',
                key: 'description',
                sortable: false, // Description might be long, not typically sorted
                canCreate: true,
                canEdit: true,
                type: FieldTypes::EDITOR->value // Use EDITOR for longer descriptions
            )
            ->addBooleanField( // Use the dedicated addBooleanField for boolean types
                label: 'Is Active',
                key: 'is_active',
                sortable: true,
                canCreate: true,
                canEdit: true
            );
    }
}
