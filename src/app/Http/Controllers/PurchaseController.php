<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Order;
use App\Models\Profile;
use App\Http\Requests\AddressRequest;
use App\Http\Requests\PurchaseRequest;
use Illuminate\Support\Facades\DB;
use Stripe\StripeClient;

class PurchaseController extends Controller
{

    public function show(Product $item)
    {
        $profile = auth()->check() ? auth()->user()->profile : null;

        return view('purchase', [
            'item' => $item,
            'profile' => $profile,
        ]);
    }

    public function address(Product $item)
    {
        $profile = auth()->user()->profile;

        return view('address', [
            'item' => $item,
            'profile' => $profile,
        ]);
    }

    public function addressUpdate(AddressRequest $request, Product $item)
    {
        $user = auth()->user();

        $profile = $user->profile ?? new Profile(['user_id' => $user->id]);

        $data = $request->validated();

        $profile->fill($data)->save();

    return redirect()
        ->route('purchase.show', $item)
        ->with('success', '住所を更新しました');
    }

    public function store(PurchaseRequest $request, Product $item)
    {
        $data = $request->validate([
            'payment_method' => 'required|in:card,convenience_store',
        ]);

        if ($item->status === 'sold') {
            return back()->with('error', 'この商品はすでに売り切れです。');
        }
        if ($item->user_id === auth()->id()) {
            return back()->with('error', '自分の商品は購入できません。');
        }

        $amount = (int) $item->price;

        $imageUrl = $item->image_url ? (str_starts_with($item->image_url, 'http')
        ? $item->image_url
        : asset($item->image_url)) : null;

        $stripe = new StripeClient(config('services.stripe.secret'));

        $session = $stripe->checkout->sessions->create([
            'mode' => 'payment',
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'jpy',
                    'unit_amount' => $amount,
                    'product_data' => [
                        'name' => $item->product_name,
                        'images' => $imageUrl ? [$imageUrl] : [],
                ],
            ],
            'quantity' => 1,
        ]],
        'customer_email' => optional(auth()->user())->email,
        'success_url' => route('purchase.success') . '?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url'  => route('purchase.cancel'),
        'metadata' => [
            'product_id' => (string) $item->id,
            'buyer_id'   => (string) auth()->id(),
        ],
        ]);

        return redirect()->away($session->url, 303);
    }


    public function success(Request $request)
    {
        $sessionId = $request->query('session_id');
    if (!$sessionId) abort(404);

        $stripe = new StripeClient(config('services.stripe.secret'));
        $session = $stripe->checkout->sessions->retrieve($sessionId, ['expand' => ['payment_intent']]);

        if ($session->payment_status !== 'paid') {
            return redirect()->route('items.index')->with('error', '決済が未完了です。');
        }

        DB::transaction(function () use ($session) {
            $buyerId   = (int) ($session->metadata->buyer_id ?? 0);
            $productId = (int) ($session->metadata->product_id ?? 0);

            Order::firstOrCreate(
                ['user_id' => $buyerId, 'product_id' => $productId],
                ['payment_method' => 'card', 'status' => 'completed']
            );

            Product::where('id', $productId)->where('status', '!=', 'sold')
                ->update(['status' => 'sold']);
        });

        return redirect()->route('mypage.index', ['tab' => 'bought'])
            ->with('success', '決済が完了しました。');
    }

    public function cancel()
    {
        return redirect()->back()->with('error', '決済をキャンセルしました。');
    }
}
