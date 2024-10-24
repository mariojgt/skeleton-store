<?php

namespace Skeleton\Store\Controllers\Backend\Product;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Skeleton\Store\Enums\PriceType;
use Skeleton\Store\Models\Product;
use Skeleton\Store\Models\Category;
use App\Http\Controllers\Controller;
use Mariojgt\Magnifier\Models\Media;
use Skeleton\Store\Enums\ProductType;
use Mariojgt\Builder\Enums\FieldTypes;
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

        return Inertia::render('BackEnd/Vendor/skeleton-store/product/index', [
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
            'custom_edit_route' => '/' . config('skeleton.route_prefix') . '/edit/product/',
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

        $dynamicCategorySearch = [
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
        ];

        return Inertia::render('BackEnd/Vendor/skeleton-store/product/edit', [
            'breadcrumb'            => $breadcrumb,
            'product'               => new ProductResource($product),
            'image_search_endpoint' => route('admin.api.media.search'),
            'dynamicCategorySearch' => $dynamicCategorySearch,
            'selected_category'     => new CategoryResource($product->category),
            'type_enum'             => ProductType::array(),
            'price_type_enum'       => PriceType::array(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        // Validate the request
        $data = $request->validate([
            'name'          => 'required',
            'slug'          => 'required',
            'category_id'   => 'required',
            'product_image' => 'required',
            'description'   => 'required',
            'price'         => 'required | numeric',
            'type'          => 'required',
            'price_type'    => 'required',
            'file_path'     => 'required',
        ]);

        $product->name        = $data['name'];
        $product->slug        = $data['slug'];
        $product->category_id = is_array($data['category_id']) ? $data['category_id'][0]['id'] : $data['category_id'];
        $product->description = $data['description'];
        $product->price       = $data['price'];
        $product->type        = $data['type'];
        $product->price_type  = $data['price_type'];
        $product->file_path   = $data['file_path'];
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
