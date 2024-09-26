<?php

namespace Skeleton\Store\Database\Seeders;

use Illuminate\Database\Seeder;
use Skeleton\Store\Models\Order;
use Skeleton\Store\Models\Product;
use Skeleton\Store\Models\Category;
use Skeleton\Store\Models\OrderItem;
use Skeleton\Store\Enums\OrderStatus;
use Mariojgt\SkeletonAdmin\Models\User;
use Skeleton\Store\Models\StoreSetting;

class StoreSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Create categories
        $categories = [
            ['name' => 'E-books', 'slug' => 'e-books'],
            ['name' => 'Video Courses', 'slug' => 'video-courses'],
        ];

        foreach ($categories as $category) {
            Category::createOrFirst($category);
        }

        // Create some products
        $products = [
            [
                'name'        => 'E-book: Laravel Basics',
                'slug'        => 'laravel-basics',
                'description' => 'Learn the basics of Laravel',
                'price'       => 19.99,
                'file_path'   => 'ebooks/laravel-basics.pdf',
                'category_id' => 1
            ],
            [
                'name'        => 'Video Course: Advanced PHP',
                'slug'        => 'advanced-php',
                'description' => 'Master advanced PHP techniques',
                'price'       => 49.99,
                'file_path'   => 'courses/advanced-php.mp4',
                'category_id' => 2
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }

        // Create a user using factory
        $user             = new User();
        $user->first_name = 'John Doe';
        $user->last_name  = 'Doe';
        $user->email      = 'johnTeste@teste.com';
        $user->password   = bcrypt('password');
        $user->save();

        // Create an order
        $order = Order::create([
            'user_id' => $user->id,
            'total'   => 69.98,
            'status'  => OrderStatus::completed->value,
        ]);

        // Create order items
        foreach (Product::all() as $product) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => 1,
                'price' => $product->price,
            ]);
        }

        // Create a default store settings
        $storeSettings = [
            'store_currency' => 'GBP',
            'store_currency_symbol' => '£',
            'store_default_tax' => 20
        ];

        foreach ($storeSettings as $key => $value) {
            StoreSetting::create([
                'key' => $key,
                'value' => $value,
            ]);
        }
    }
}
