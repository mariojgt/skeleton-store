<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Skeleton\Store\Models\Product;
use Skeleton\Store\Models\ProductResource;

return new class extends Migration
{
    public function up()
    {
        // First, migrate existing file_path data to product_resources
        $products = Product::whereNotNull('file_path')->get();

        foreach ($products as $product) {
            if (!empty($product->file_path)) {
                ProductResource::create([
                    'product_id' => $product->id,
                    'title' => 'Product File',
                    'description' => 'Migrated from product file_path',
                    'resource_type' => 'file',
                    'file_path' => $product->file_path
                ]);
            }
        }

        // Then remove the column
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('file_path');
            $table->boolean('free_with_subscription')->default(false);
        });
    }

    public function down()
    {
        // Add the column back
        Schema::table('products', function (Blueprint $table) {
            $table->string('file_path')->nullable();
            $table->dropColumn('free_with_subscription');
        });

        // Restore the last file_path from product_resources if it exists
        $resources = ProductResource::where('resource_type', 'file')
            ->where('description', 'Migrated from product file_path')
            ->get();

        foreach ($resources as $resource) {
            Product::where('id', $resource->product_id)
                ->update(['file_path' => $resource->file_path]);
        }
    }
};
