<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\Comment;
use App\Models\Like;
use App\Http\Requests\CommentRequest;
use Illuminate\Database\QueryException;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $tab     = $request->query('tab', 'all');
        $keyword = $request->query('keyword', '');
        $userId  = Auth::id();

        if ($tab === 'mylist') {
            if (!Auth::check()) {
                $products = Product::query()
                    ->whereRaw('1=0')
                    ->paginate(20);

                return view('index', [
                    'products' => $products,
                    'tab'      => 'mylist',
                    'keyword'  => $keyword,
                ]);
            }

            if (!Auth::user()->hasVerifiedEmail()) {
                return redirect()->route('verification.notice');
            }


            $products = Product::with(['brand', 'categories'])
                ->withCount(['likes', 'comments'])
                ->withCount([
                    'likes as liked_by_user' => function ($likesQuery) use ($userId) {
                        $likesQuery->where('user_id', $userId);
                    }
                ])
                ->whereHas('likes', fn ($likesQuery) => $likesQuery->where('user_id', $userId))
                ->nameContains($keyword)
                ->latest('id')
                ->paginate(20);

            return view('index', [
                'products' => $products,
                'tab'      => 'mylist',
                'keyword'  => $keyword,
            ]);
        }

        $products = Product::with(['brand', 'categories'])
            ->withCount(['likes', 'comments'])
            ->when($userId, function ($productQuery) use ($userId) {
                $productQuery->withCount([
                    'likes as liked_by_user' => function ($likesQuery) use ($userId) {
                        $likesQuery->where('user_id', $userId);
                    }
                ]);
            })
            ->notOwnedBy(Auth::id())
            ->nameContains($keyword)
            ->latest('id')
            ->paginate(20);

        return view('index', [
            'products' => $products,
            'tab'      => 'all',
            'keyword'  => $keyword,
        ]);
    }

    public function show(Product $item)
    {
        $userId = Auth::id();

        $item->load(['brand', 'categories', 'user', 'comments.user']);

        $item->loadCount(['likes', 'comments']);

        if ($userId) {
            $item->loadCount([
                'likes as liked_by_user' => function ($likesQuery) use ($userId) {
                    $likesQuery->where('user_id', $userId);
                }
            ]);
        }

        return view('item', ['item' => $item]);
    }

    public function storeComment(CommentRequest $request, Product $item)
    {
        $data = $request->validated();

        Comment::create([
            'product_id' => $item->id,
            'user_id'    => Auth::id(),
            'body'       => $data['body'],
        ]);

        return back();
    }

    public function toggleLike(Product $item)
    {
        $userId = Auth::id();
        if (!$userId) {
            return redirect()->route('login');
        }

        $query = Like::where('user_id', $userId)
            ->where('product_id', $item->id);

        if ($query->exists()) {
            $query->delete();
            return back()->with('like_status', 'unliked');
        }

        try {
            Like::create([
                'user_id'    => $userId,
                'product_id' => $item->id,
            ]);
        } catch (QueryException $exception) {
            if ($exception->getCode() !== '23000') {
                throw $exception;
            }
        }

        return back();
    }
}
