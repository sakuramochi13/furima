<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use App\Models\User;

class ProductsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $userIds = User::pluck('id')->all();

        $rows = [
            [
                'product_name'   => '腕時計',
                'price'          => 15000,
                'description'    => 'スタイリッシュなデザインのメンズ腕時計',
                'image_url'      => 'products/Armani+Mens+Clock.jpg',
                'condition'      => 'excellent',
                'brand_name'     => 'Rolax',
                'category_names' => ['ファッション','メンズ'],
            ],
            [
                'product_name'   => 'HDD',
                'price'          => 5000,
                'description'    => '高速で信頼性の高いハードディスク',
                'image_url'      => 'products/HDD+Hard+Disk.jpg',
                'condition'      => 'very_good',
                'brand_name'     => '西芝',
                'category_names' => ['家電'],
                'status' => 'sold',
            ],
            [
                'product_name'   => '玉ねぎ3束',
                'price'          => 300,
                'description'    => '新鮮な玉ねぎ3束のセット',
                'image_url'      => 'products/iLoveIMG+d.jpg',
                'condition'      => 'good',
                'brand_name'     => 'なし',
                'category_names' => ['キッチン','ハンドメイド'],
            ],
            [
                'product_name'   => '革靴',
                'price'          => 4000,
                'description'    => 'クラシックなデザインの革靴',
                'image_url'      => 'products/Leather+Shoes+Product+Photo.jpg',
                'condition'      => 'good',
                'brand_name'     => null,
                'category_names' => ['メンズ','ファッション'],
            ],
            [
                'product_name'   => 'ノートPC',
                'price'          => 45000,
                'description'    => '高性能なノートパソコン',
                'image_url'      => 'products/Living+Room+Laptop.jpg',
                'condition'      => 'excellent',
                'brand_name'     => null,
                'category_names' => ['家電'],
            ],
            [
                'product_name'   => 'マイク',
                'price'          => 8000,
                'description'    => '高音質のレコーディング用マイク',
                'image_url'      => 'products/Music+Mic+4632231.jpg',
                'condition'      => 'very_good',
                'brand_name'     => 'なし',
                'category_names' => ['家電'],
            ],
            [
                'product_name'   => 'ショルダーバッグ',
                'price'          => 3500,
                'description'    => 'おしゃれなショルダーバッグ',
                'image_url'      => 'products/Purse+fashion+pocket.jpg',
                'condition'      => 'good',
                'brand_name'     => null,
                'category_names' => ['ファッション','レディース'],
            ],
            [
                'product_name'   => 'タンブラー',
                'price'          => 500,
                'description'    => '使いやすいタンブラー',
                'image_url'      => 'products/Tumbler+souvenir.jpg',
                'condition'      => 'poor',
                'brand_name'     => 'なし',
                'category_names' => ['キッチン'],
            ],
            [
                'product_name'   => 'コーヒーミル',
                'price'          => 4000,
                'description'    => '手動のコーヒーミル',
                'image_url'      => 'products/Waitress+with+Coffee+Grinder.jpg',
                'condition'      => 'excellent',
                'brand_name'     => 'Starbacks',
                'category_names' => ['家電','キッチン'],
            ],
            [
                'product_name'   => 'メイクセット',
                'price'          => 2500,
                'description'    => '便利なメイクアップセット',
                'image_url'      => 'products/外出メイクアップセット.jpg',
                'condition'      => 'very_good',
                'brand_name'     => null,
                'category_names' => ['レディース','コスメ'],
            ],
        ];

        foreach ($rows as $row) {

            $brandId = null;
            $brandName = $row['brand_name'] ?? null;

            if (!empty($brandName)) {
                if ($brandName === 'なし') {
                    $brandId = Brand::firstOrCreate(['name' => 'なし'])->id;
                } else {
                    $brandId = Brand::firstOrCreate(['name' => $brandName])->id;
                }
            }

            $categoryIds = Category::whereIn('name', $row['category_names'])->pluck('id')->all();

            $userId = $userIds[array_rand($userIds)];

            $product = Product::create([
                'user_id'      => $userId,
                'brand_id'     => $brandId,
                'condition'    => $row['condition'],
                'product_name' => $row['product_name'],
                'description'  => $row['description'],
                'price'        => $row['price'],
                'status'       => $row['status'] ?? 'listed',
                'image_url'    => $row['image_url'],
            ]);

            $product->categories()->syncWithoutDetaching($categoryIds);

        }
    }
}
