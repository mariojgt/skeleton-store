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
        // Set endpoints
        ->setEndpoints(
            listEndpoint: route('admin.api.generic.table'),
            deleteEndpoint: route('admin.api.generic.table.delete'),
            createEndpoint: route('admin.api.generic.table.create'),
            editEndpoint: route('admin.api.generic.table.update')
        )
        // Set model
        ->setModel(Product::class)
        // Set permissions
        ->setPermissions(
            guard: 'skeleton_admin',
            type: 'permission',
            permissions: [
                'store'  => 'create-permission',
                'update' => 'edit-permission',
                'delete' => 'delete-permission',
                'index'  => 'read-permission',
            ]
        )
        ->setCustomEditRoute('/' . config('skeleton.route_prefix') . '/edit/product/')
        ->build();

        return Inertia::render('BackEnd/Vendor/skeleton-store/product/index', [
            'title'      => 'Product | 📦',
            'table_name' => 'Product',
            'breadcrumb' => $breadcrumb,
            ...$formConfig
        ]);
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

        // Initialize form helper
        $form = new FormHelper();
        $formConfig = $form
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
            ->build();

        $dynamicCategorySearch = $formConfig['columns'][0];

        return Inertia::render('BackEnd/Vendor/skeleton-store/product/edit', [
            'breadcrumb'            => $breadcrumb,
            'product'               => new ProductResource($product),
            'image_search_endpoint' => route('admin.api.media.search'),
            'dynamicCategorySearch' => $dynamicCategorySearch,
            'selected_category'     => new CategoryResource($product->category),
            'type_enum'             => ProductType::array(),
            'price_type_enum'       => PriceType::array(),
            'apiKey'            => config('gamehub.tiny_mce_key'),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        // Validate the request
        $data = $request->validate([
            'name'                   => 'required',
            'slug'                   => 'required',
            'category_id'            => 'required',
            'product_image'          => 'required',
            'description'            => 'required',
            'price'                  => 'required | numeric',
            'type'                   => 'required',
            'price_type'             => 'required',
            'free_with_subscription' => 'required'
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

        return redirect()->route('admin.store.product.index');
    }
}
