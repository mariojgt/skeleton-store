<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Skeleton\Store\Enums\CapabilityType;
use Skeleton\Store\Models\Capability;

class CapabilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create capabilities based on the enum
        foreach (CapabilityType::cases() as $capabilityType) {
            Capability::updateOrCreate(
                ['slug' => $capabilityType->value],
                [
                    'name' => $capabilityType->label(),
                    'description' => $capabilityType->description(),
                    'is_active' => true,
                ]
            );
        }
    }
}
