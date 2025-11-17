@extends('layouts/app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/item.css')}}">
@endsection

@section('header_nav')
<div class="nav-group">
    <form class="search-form" action="{{ route('items.index') }}" method="GET">
        <input class="search-form__text" type="text" name="keyword" value="{{ old('keyword', $keyword ?? '') }}" placeholder="なにをお探しですか？" autocomplete="off">
    </form>
    <nav class="nav-item">
        @auth
            <form class="gate-form" action="{{ route('logout') }}" method="POST">
                @csrf
                <button class="nav-link" type="submit">ログアウト</button>
            </form>
        @else
            <button class="nav-link" type="button" onclick="location.href='{{ route('login') }}'">ログイン</button>
        @endauth
        <button class="nav-link" type="button" onclick="location.href='/mypage'">マイページ</button>
        <button class="sell-button" type="button" onclick="location.href='/sell'">出品</button>
    </nav>
</div>
@endsection


@section('content')
<div class="item-container">
    <div class="item-section" style="position: relative;">
        <img class="item-section__img" src="{{ $item->image_url }}" alt="{{ $item->product_name }}">
        @if ($item->status === 'sold')
            <div class="item-section__sold-tag">SOLD</div>
        @endif
    </div>
    <div class="item-section">
        <div class="item-group">
            <h1 class="item-group__product_name">{{ $item->product_name }}</h1>
            <p class="item-group__brand-name">{{ optional($item->brand)->name }}</p>
            <p class="item-group__price">{{ number_format($item->price) }}</p>
            <div class="item-reaction">
                <div class="item-reaction__like">
                    <form class="like-form" action="{{ route('item.like', $item->id) }}" method="POST">
                        @csrf
                        <button class="item-reaction__like--icon" type="submit" title="いいね">
                            @if (($item->liked_by_user ?? 0) > 0)
                            <img src="{{ asset('images/icon-star-2.svg') }}" alt="like" class="like-icon">
                            @else
                            <img src="{{ asset('images/icon-star-1.svg') }}" alt="not liked" class="like-icon">
                            @endif
                        </button>
                    </form>
                    <span class="item-reaction__like--counter">{{ $item->likes_count }}</span>
                </div>
                <div class="item-reaction__comment">
                    <button class="item-reaction__comment--icon" type="submit">
                        <img src="{{ asset('images/icon-comment.svg') }}" alt="comment" class="comment-icon">
                    </button>
                    <span class="item-reaction__comment--counter">{{ $item->comments_count }}</span>
                </div>
            </div>
            <a href="{{ url('/purchase/' . $item->id) }}" class="item-group__buy">購入手続きへ</a>
            <h2 class="item-group__label">商品説明</h2>
            <p class="item-group__description">{!! nl2br(e($item->description)) !!}</p>
            <h2 class="item-group__label">商品の情報</h2>
            <div class="item-group__info">
                <h3 class="item-group__info--label">カテゴリー</h3>
                <div class="item-group__info--field">
                    @foreach ($item->categories as $category)
                        <span class="item-group__info--category">{{ $category->name }}</span>
                    @endforeach
                </div>
            </div>
            <div class="item-group__info">
                <h3 class="item-group__info--label">商品の状態</h3>
                <p class="item-group__info--condition">{{ $item->condition_label }}</p>
            </div>
            <h2 class="item-group__label">コメント(<span>{{ $item->comments_count }}</span>)</h2>
            <div id="comments">
                @foreach ($item->comments as $comment)
                <div>
                    <div class="item-group__user">
                        <img class="item-group__user--img" src="{{ optional($comment->user->profile)->image_url ?? asset('images/default-user.png') }}" alt="ユーザー画像">
                        <p class="item-group__user--name">
                            {{ $comment->user->username ?? $comment->user->name ?? 'ユーザー' }}
                        </p>
                    </div>
                    <p class="item-group__comment">{{ $comment->body }}</p>
                </div>
                @endforeach
            </div>
            <form action="{{ route('comments.store', ['item' => $item->id]) }}" method="POST">
                @csrf
                <label class="comment-form__label" for="comment">商品へのコメント</label>
                <textarea class="comment-form__text" name="body" id="comment">{{ old('body') }}</textarea>
                <div class="comment-form__error">
                    @error('body')
                    <p class="comment-form__error--message" role="alert">{{ $message }}</p>
                    @enderror
                </div>
                <button class="comment-form__btn" type="submit">コメントを送信する</button>
            </form>
        </div>
    </div>
</div>
@endsection