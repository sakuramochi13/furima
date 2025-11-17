<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProfilesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('profiles')->insert([
            [
                'user_id' => 1,
                'profile_image_url' => null,
                'postal_code' => '123-4567',
                'address' => '東京都渋谷区1-2-3',
                'building' => '渋谷マンション101',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'user_id' => 3,
                'profile_image_url' => null,
                'postal_code' => '765-4321',
                'address' => '大阪府大阪市北区4-5-6',
                'building' => null, // 建物名なし
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'user_id' => 4,
                'profile_image_url' => null,
                'postal_code' => '987-6543',
                'address' => '愛知県名古屋市中区7-8-9',
                'building' => '名古屋タワー15F',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
