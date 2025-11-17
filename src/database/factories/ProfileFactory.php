<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

class ProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id'           => User::factory(),
            'postal_code'       => '123-4567',
            'address'           => 'テスト県テスト市1-2-3',
            'building'          => 'テストビル101',
            'profile_image_url' => '/storage/profiles/test-profile.png',
        ];
    }
}
