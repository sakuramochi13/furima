@extends('layouts/app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/sell.css')}}">
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
<h1 class="sell-title">商品の出品</h1>
<div class="sell-container">
    <form class="sell-form" action="{{ route('sell.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <dl class="sell-group">
            <dt class="sell-label__sub">商品画像</dt>
            <dd class="sell-group__img" id="imageBox">
                <input type="file" id="productImage" name="image" accept=".jpeg,.jpg,.png" hidden>
                <button type="button" class="sell-group__img--btn" onclick="document.getElementById('productImage').click()">画像を選択する
                </button>
                <div id="preview-container" class="sell-group__img--preview"></div>
            </dd>
            <dd class="sell-error">@error('image'){{ $message }}@enderror</dd>
            <div class="sell-section">
                <h2 class="sell-label">商品の詳細</h2>
            </div>
            <dt class="sell-label__sub">カテゴリー</dt>
            <dd class="sell-group__category">
                @foreach($categories as $category)
                    <input
                        type="checkbox"
                        name="category_ids[]"
                        value="{{ $category->id }}"
                        id="cat-{{ $category->id }}"
                        class="sell-group__category--checkbox"
                        {{ in_array($category->id, old('category_ids', [])) ? 'checked' : '' }}
                    >
                    <label for="cat-{{ $category->id }}" class="sell-group__category--checkbox-label">
                        {{ $category->name }}
                    </label>
                    @endforeach
            </dd>
            <dd class="sell-error">@error('category_ids'){{ $message }}@enderror</dd>
            <dt class="sell-label__sub">商品の状態</dt>
            <dd class="sell-group__condition">
                <select class="sell-group__condition--select" name="condition" id="" value="">
                    <option value="" disabled {{ old('condition') ? '' : 'selected' }}>選択してください</option>
                    @foreach($conditionOptions as $value => $label)
                    <option value="{{ $value }}" {{ old('condition') === $value ? 'selected' : '' }}>
                        {{ $label }}
                        </option>
                    @endforeach
                </select>
            </dd>
            <dd class="sell-error">@error('condition'){{ $message }}@enderror</dd>
            <div class="sell-section">
                <h2 class="sell-label">商品名と説明</h2>
            </div>
            <dt class="sell-label__sub">商品名</dt>
            <dd class="sell-group__parts">
                <input type="text" class="sell-group__parts--input" name="product_name" value="{{ old('product_name') }}" />
            </dd>
            <dd class="sell-error">@error('product_name'){{ $message }}@enderror</dd>
            <dt class="sell-label__sub">ブランド名</dt>
            <dd class="sell-group__parts">
                <input type="text" class="sell-group__parts--input" name="brand_name" value="{{ old('brand_name') }}" />
            </dd>
            <dd class="sell-error">@error('brand_name'){{ $message }}@enderror</dd>
            <dt class="sell-label__sub">商品の説明</dt>
            <dd class="sell-group__description">
                <textarea name="description" class="sell-group__description--textarea">{{ old('description') }}</textarea>
            </dd>
            <dd class="sell-error">@error('description'){{ $message }}@enderror</dd>
            <dt class="sell-label__sub">販売価格</dt>
            <dd class="sell-group__parts">
                <input type="text" name="price" class="sell-group__parts--input" placeholder="¥" value="{{ old('price') }}" />
            </dd>
            <dd class="sell-error">@error('price'){{ $message }}@enderror</dd>
            <button class="sell-btn" type="submit">出品する</button>
        </dl>
    </form>
</div>
<script>
const input = document.getElementById('productImage');
const previewContainer = document.getElementById('preview-container');
const imageBox = document.getElementById('imageBox');

input.addEventListener('change', function (event) {
    const file = event.target.files[0];
    previewContainer.innerHTML = '';

    if (file) {
        const reader = new FileReader();

        reader.onload = function (e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.alt = '選択された商品画像';
            img.classList.add('sell-group__img--preview-img');
            previewContainer.appendChild(img);
        };

        reader.readAsDataURL(file);

        imageBox.classList.add('has-image');
    } else {
        imageBox.classList.remove('has-image');
    }
});
</script>
@endsection


