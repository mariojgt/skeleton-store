<?php

namespace Skeleton\Store\Database\Seeders;

use Illuminate\Database\Seeder;
use Mariojgt\SkeletonAdmin\Models\Navigation;

class NavigationSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $menuOptions = [
            [
                'parent_id'  => null,
                'menu_label' => 'Product',
                'route'      => 'admin.store.product.index',
                'icon'       => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap = "round" stroke-linejoin = "round" d = "m7.875 14.25 1.214 1.942a2.25 2.25 0 0 0 1.908 1.058h2.006c.776 0 1.497-.4 1.908-1.058l1.214-1.942M2.41 9h4.636a2.25 2.25 0 0 1 1.872 1.002l.164.246a2.25 2.25 0 0 0 1.872 1.002h2.092a2.25 2.25 0 0 0 1.872-1.002l.164-.246A2.25 2.25 0 0 1 16.954 9h4.636M2.41 9a2.25 2.25 0 0 0-.16.832V12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 12V9.832c0-.287-.055-.57-.16-.832M2.41 9a2.25 2.25 0 0 1 .382-.632l3.285-3.832a2.25 2.25 0 0 1 1.708-.786h8.43c.657 0 1.281.287 1.709.786l3.284 3.832c.163.19.291.404.382.632M4.5 20.25h15A2.25 2.25 0 0 0 21.75 18v-2.625c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125V18a2.25 2.25 0 0 0 2.25 2.25Z" />
                            </svg>',
                'guard'      => 'skeleton_admin',
            ],
            [
                'parent_id'  => null,
                'menu_label' => 'Product Category',
                'route'      => 'admin.store.product-category.index',
                'icon'       => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />
                </svg>
                ',
                'guard'      => 'skeleton_admin',
            ]
        ];

        // Loop the menuOptions and create it
        foreach ($menuOptions as $role) {
            // First or create the role
            Navigation::firstOrCreate([
                'parent_id'  => $role['parent_id'],
                'menu_label' => $role['menu_label'],
                'route'      => $role['route'],
                'icon'       => $role['icon'],
                'guard'      => $role['guard'],
            ]);
        }
    }
}
