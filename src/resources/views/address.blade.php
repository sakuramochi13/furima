@extends('layouts/app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/address.css')}}">
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
<h1 class="address-title">住所の変更</h1>
<div class="address-container">
    <form class="update-form" action="{{ route('purchase.address.update', $item) }}" method="POST">
        @csrf
        <dl class="update-form__inner">
            <dt class="update-form__label">郵便番号</dt>
            <dd class="update-form__body">
                <input class="update-form__body--text" type="text" name="postal_code"
                value="{{ old('postal_code', $profile->postal_code ?? '') }}">
            </dd>
            <dd class="update-form__error">
                @error('postal_code') {{ $message }} @enderror
            </dd>

            <dt class="update-form__label">住所</dt>
            <dd class="update-form__body">
                <input class="update-form__body--text" type="text" name="address"
                value="{{ old('address', $profile->address ?? '') }}">
            </dd>
            <dd class="update-form__error">
                @error('address') {{ $message }} @enderror
            </dd>
            <dt class="update-form__label">建物名</dt>
            <dd class="update-form__body">
                <input class="update-form__body--text" type="text" name="building"
                value="{{ old('building', $profile->building ?? '') }}">
            </dd>
            <dd class="update-form__error"></dd>
        </dl>
        <button class="update-form__btn">更新する</button>
    </form>
</div>
@endsection