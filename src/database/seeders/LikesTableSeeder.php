<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Like;
use App\Models\Product;
use App\Models\User;

class LikesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::find(1);

        if (!$user) {
            $this->command->warn('ユーザーID=1 が見つかりません。');
            return;
        }

        $COUNT = 3;

        Like::where('user_id', $user->id)->delete();

        $productIds = Product::query()
            ->where('user_id', '!=', $user->id)
            ->inRandomOrder()
            ->limit($COUNT)
            ->pluck('id');

        if ($productIds->count() < $COUNT) {
            $this->command->warn("自分以外の出品が {$COUNT} 件ありません。取得: {$productIds->count()} 件");
        }

        foreach ($productIds as $pid) {
            Like::firstOrCreate([
                'user_id'    => $user->id,
                'product_id' => $pid,
            ]);
        }
    }
}
