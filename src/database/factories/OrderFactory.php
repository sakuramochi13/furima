<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id'    => \App\Models\User::factory(),
            'product_id' => \App\Models\Product::factory(),
            'payment_method' => 'card',
            'status'         => 'completed',
        ];
    }
}
