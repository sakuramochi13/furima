<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ExhibitionRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Brand;
use Illuminate\Support\Facades\Storage;

class SellController extends Controller
{
    public function create()
    {
        $categories = Category::orderBy('name')->get();

        $conditionOptions = [
            'excellent'  => '良好',
            'very_good'  => '目立った傷や汚れなし',
            'good'       => 'やや傷や汚れあり',
            'poor'       => '状態が悪い',
        ];

        $brands = Brand::orderBy('name')->get();

        return view('sell', compact('categories', 'conditionOptions', 'brands'));
    }

    public function store(ExhibitionRequest $request)
    {
        $data = $request->validated();

        $path = $request->file('image')->store('products', 'public');

        $product = new Product();
        $product->user_id      = auth()->id();
        $product->product_name = $data['product_name'];
        $product->description  = $data['description'];
        $product->price        = $data['price'];
        $product->condition    = $data['condition'];
        $product->image_url    = Storage::url($path);
        $product->status       = 'listed';

        if (!empty($data['brand_name'])) {
            $brand = \App\Models\Brand::firstOrCreate(['name' => $data['brand_name']]);
            $product->brand_id = $brand->id;
        } else {
            $product->brand_id = null;
        }

        $product->save();

        if (!empty($data['category_ids'])) {
            $product->categories()->sync($data['category_ids']);
        }

        return redirect()
            ->route('items.index')
            ->with('success', '出品が完了しました。');
    }
}
