<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Brand;

class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id'      => User::factory(),
            'brand_id'     => Brand::factory(),
            'product_name' => $this->faker->words(3, true),
            'description'  => $this->faker->sentence(),
            'price'        => $this->faker->numberBetween(100, 10000),
            'condition'    => 'excellent',
            'image_url'    => '/storage/products/test.jpg',
            'status'       => 'listed',
        ];
    }
}
