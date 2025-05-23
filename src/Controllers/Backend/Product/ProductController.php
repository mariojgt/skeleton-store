<?php

namespace Skeleton\Store\Controllers\Backend\Product;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Skeleton\Store\Models\Product;
use Skeleton\Store\Enums\PriceType;
use Skeleton\Store\Models\Category;
use App\Http\Controllers\Controller;
use Mariojgt\Magnifier\Models\Media;
use Skeleton\Store\Enums\ProductType;
use Mariojgt\Builder\Enums\FieldTypes;
use Mariojgt\Builder\Helpers\FormHelper;
use Skeleton\Store\Resource\ProductResource;
use Skeleton\Store\Resource\CategoryResource;
use Mariojgt\SkeletonAdmin\Enums\PermissionEnum;
use Mariojgt\SkeletonAdmin\Controllers\Backend\Web\Crud\GenericCrudController; // Added this use statement

class ProductController extends GenericCrudController // Changed to extend GenericCrudController
{
    public function __construct()
    {
        $this->title = 'Product | 📦';
        $this->model = Product::class;
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
                type: FieldTypes::SLUG->value,
                unique: true
            )
            ->addField(
                label: 'Price',
                key: 'price',
                sortable: true,
                canCreate: true,
                canEdit: true,
                type: FieldTypes::NUMBER->value
            )
            ->addField(
                label: 'Category',
                key: 'category_id',
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
                model: encrypt(Category::class),
                singleSearch: true,
                displayKey: 'name'
            )
            ->addField(
                label: 'Image',
                key: 'image',
                sortable: false,
                canCreate: true,
                canEdit: true,
                nullable: true,
                type: FieldTypes::MEDIA->value,
                endpoint: route('admin.api.media.search')
            )
            // Keep the custom edit route to link to the specialized edit page for full product management
            ->setCustomEditRoute('/' . config('skeleton.route_prefix') . '/edit/product/');
    }

    public function edit(Request $request, Product $product)
    {
        $breadcrumb = [
            [
                'label' => 'Product',
                'url'   => route('admin.store.product.index'),
            ],
            [
                'label' => 'Edit',
            ]
        ];

        // The form helper from the original edit method was only used to define dynamicCategorySearch.
        // We can just define dynamicCategorySearch directly since it's only one field.
        $dynamicCategorySearch = [
            'label'        => 'Category',
            'key'          => 'category_id',
            'sortable'     => false,
            'canCreate'    => true,
            'canEdit'      => true,
            'nullable'     => true,
            'type'         => FieldTypes::MODEL_SEARCH->value, // Use enum value
            'endpoint'     => route('admin.api.generic.table'),
            'columns'      => [
                [
                    'key'      => 'id',
                    'sortable' => false
                ],
                [
                    'key'      => 'name',
                    'sortable' => true,
                ],
            ],
            'model'        => encrypt(Category::class),
            'singleSearch' => true,
            'displayKey'   => 'name' // Added displayKey based on FormHelper structure
        ];

        return Inertia::render('BackEnd/Vendor/skeleton-store/product/edit', [
            'breadcrumb'            => $breadcrumb,
            'product'               => new ProductResource($product),
            'image_search_endpoint' => route('admin.api.media.search'),
            'dynamicCategorySearch' => $dynamicCategorySearch,
            'selected_category'     => new CategoryResource($product->category),
            'type_enum'             => ProductType::array(),
            'price_type_enum'       => PriceType::array(),
            'apiKey'                => config('gamehub.tiny_mce_key'),
            'update_route'          => route('admin.store.product.update', $product->id), // Pass update route to Inertia component
        ]);
    }

    public function update(Request $request, Product $product)
    {
        // Validate the request
        $data = $request->validate([
            'name'                   => 'required',
            'slug'                   => 'required',
            'category_id'            => 'required',
            'product_image'          => 'required|array', // Added array validation for product_image
            'description'            => 'required',
            'price'                  => 'required|numeric',
            'type'                   => 'required',
            'price_type'             => 'required',
            'free_with_subscription' => 'required|boolean' // Added boolean validation
        ]);

        $product->name                  = $data['name'];
        $product->slug                  = $data['slug'];
        $product->category_id           = is_array($data['category_id']) ? $data['category_id'][0]['id'] : $data['category_id'];
        $product->description           = $data['description'];
        $product->price                 = $data['price'];
        $product->type                  = $data['type'];
        $product->price_type            = $data['price_type'];
        $product->free_with_subscription = $data['free_with_subscription'];
        $product->save();

        // Get all the ids form the $data['course_image']
        $ids = array_column($data['product_image'], 'id');
        // Delete all associated media
        $product->media()->delete();
        // Attach new media
        $media = Media::whereIn('id', $ids)->get();
        foreach ($media as $mediaItem) {
            $product->media()->create([
                'media_id'   => $mediaItem->id,
                'model_id'   => $product->id,
                'model_type' => Product::class,
            ]);
        }

        return redirect()->route('admin.store.product.index')->with('success', 'Product updated successfully.');
    }
}
