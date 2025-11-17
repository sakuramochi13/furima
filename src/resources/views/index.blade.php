@extends('layouts/app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css')}}">
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
<div class="menu">
    <a href="{{ route('items.index', array_filter(['keyword' => $keyword ?? null])) }}"
    class="menu-suggest menu-effect {{ ($tab ?? 'all') === 'all' ? 'is-active' : '' }}">おすすめ</a>

    <a href="{{ route('items.index', array_filter(['tab' => 'mylist', 'keyword' => $keyword ?? null])) }}"
    class="menu-mylist menu-effect {{ ($tab ?? 'all') === 'mylist' ? 'is-active' : '' }}">マイリスト</a>
</div>
<div>
    @foreach ($products as $product)
        <div class="card">
            <div class="card__img">
                <img class="card__img--panel"
                src="{{ $product->image_url }}"
                alt="{{ $product->product_name }}" />
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