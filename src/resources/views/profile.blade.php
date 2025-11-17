@extends('layouts/app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/profile.css')}}">
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
<h1 class="profile-title">プロフィール設定</h1>
<div class="profile-container">
    <div class="profile-section">
        <div class="profile-section__left">
            <img class="profile-section__left--img"
                src="{{ $profile->profile_image_url ?? '' }}" alt="">
        </div>
        <div class="profile-section__right">
            <form class="profile-form" id="imageForm"
            action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="file" name="profile_image" accept=".jpeg,.jpg,.png" style="display:none" id="profileImageInput">
            <button class="profile-section__right--btn" type="button" onclick="document.getElementById('profileImageInput').click();">画像を選択する</button>
            @error('profile_image')
                <div class="profile-form__error">{{ $message }}</div>
            @enderror
        </div>
    </div>
        <dl class="profile-form__inner">
            <dt class="profile-form__label">ユーザー名</dt>
            <dd class="profile-form__body">
                <input class="profile-form__body--text" type="text" name="name"
                value="{{ old('name', $user->name) }}">
            </dd>
            <dd class="profile-form__error">
                @error('name') {{ $message }} @enderror
            </dd>
            <dt class="profile-form__label">郵便番号</dt>
            <dd class="profile-form__body">
                <input class="profile-form__body--text" type="text" name="postal_code"
                placeholder="例）123-4567"
                value="{{ old('postal_code', optional($profile)->postal_code) }}">
            </dd>
            <dd class="profile-form__error">
                @error('postal_code') {{ $message }} @enderror
            </dd>
            <dt class="profile-form__label">住所</dt>
            <dd class="profile-form__body">
                <input class="profile-form__body--text" type="text" name="address"
                value="{{ old('address', optional($profile)->address) }}">
            </dd>
            <dd class="profile-form__error">
                @error('address') {{ $message }} @enderror
            </dd>
            <dt class="profile-form__label">建物名</dt>
            <dd class="profile-form__body">
                <input class="profile-form__body--text" type="text" name="building"
                value="{{ old('building', optional($profile)->building) }}">
            </dd>
            <dd class="profile-form__error">
                @error('building') {{ $message }} @enderror
            </dd>
        </dl>
        <button class="profile-form__btn" type="submit">更新する</button>
    </form>
</div>
@endsection