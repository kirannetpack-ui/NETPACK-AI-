<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call the UserSeeder
        $this->call([
            UserSeeder::class,
            // Add other seeders here if you have them
            // DeliverySeeder::class,
            // ShipmentSeeder::class,
            // etc.
        ]);
    }
}