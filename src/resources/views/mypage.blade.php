@extends('layouts/app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/mypage.css')}}">
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
<div class="mypage-container">
    <div class="mypage-left">
        <img class="mypage-left__img"
        src="{{ $profile?->profile_image_url ?? asset('images/default-user.png') }}" alt="">
    </div>
    <div class="mypage-middle">
        <p class="mypage-middle__name">{{ $user->name }}</p>
    </div>
    <div class="mypage-right">
        <a class="mypage-right__btn" href="{{ route('profile.edit') }}">プロフィールを編集</a>
    </div>
</div>
<div class="mypage-menu">
    <a class="mypage-menu__listed menu-effect {{ ($tab ?? 'listed') === 'listed' ? 'is-active' : '' }}"
        href="{{ route('mypage.index', ['tab' => 'listed']) }}">
        出品した商品
    </a>
    <a class="mypage-menu__bought menu-effect {{ ($tab ?? '') === 'bought' ? 'is-active' : '' }}"
        href="{{ route('mypage.index', ['tab' => 'bought']) }}">
        購入した商品
    </a>
</div>
<div>
    @foreach($products as $product)
        <div class="card">
            <div class="card-img">
                <img class="card-img__panel"
                    src="{{ $product->image_url }}"
                    alt="{{ $product->product_name }}">
                @if ($product->status === 'sold')
                    <div class="card__sold-tag">SOLD</div>
                @endif
            </div>
            <div class="card__content">
                <a class="card__content--product-name"
                    href="{{ route('items.show', ['item' => $product->id]) }}">
                    {{ $product->product_name }}
                </a>
            </div>
        </div>
    @endforeach
</div>
@endsection
