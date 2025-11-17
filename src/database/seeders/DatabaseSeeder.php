<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            CategoriesTableSeeder::class,
            BrandsTableSeeder::class,
            UsersTableSeeder::class,
            ProfilesTableSeeder::class,
            ProductsTableSeeder::class,
            LikesTableSeeder::class,
        ]);
    }
}
