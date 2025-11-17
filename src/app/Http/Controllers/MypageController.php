<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\Order;
use App\Models\Profile;

class MypageController extends Controller
{

    public function index(Request $request)
    {
        $user = Auth::user();
        $profile = Profile::firstWhere('user_id', $user->id);

        $tab = $request->query('tab', 'listed');

        if ($tab === 'bought') {
            $orders = Order::with('product')
            ->where('user_id', $user->id)
            ->latest()
            ->get();

            $products = $orders->pluck('product')->filter()->values(); // Collection

            return view('mypage', compact('user', 'profile', 'tab', 'products'));
        }

        $products = Product::where('user_id', $user->id)
            ->latest()
            ->get();

        return view('mypage', compact('user', 'profile', 'tab', 'products'));
    }
}
