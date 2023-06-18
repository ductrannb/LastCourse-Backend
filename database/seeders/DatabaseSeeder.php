<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Artisan::call("command:imageSeeder");
        Artisan::call("command:userSeeder");
        Artisan::call("command:shopSeeder");
        Artisan::call("command:categorySeeder");
        Artisan::call("command:carrierSeeder");
        Artisan::call("command:conditionSeeder");
        Artisan::call("command:warehouseSeeder");

        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
