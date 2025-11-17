@extends('layouts/app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/purchase.css')}}">
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
<div class="main-container">
    <div class="main-group">
        <div class="top-section">
            <div class="top-section__img">
                <img class="top-section__img--size" src="{{ $item->image_url }}" alt="{{ $item->product_name }}">
            </div>
            <div class="top-section__contents">
                <h1 class="top-section__contents--name">{{ $item->product_name }}</h1>
                <p class="top-section__contents--price">{{ number_format($item->price) }}</p>
            </div>
        </div>
        <div class="middle-section">
            <h2 class="middle-section__payment">支払い方法</h2>
            <form action="{{ route('purchase.store', $item) }}" method="POST">
                @csrf
                <div class="payment-form__method">
                    <select class="payment-form__method--select" id="paymentSelect" name="payment_method" value="card">
                        <option value="" selected disabled>選択してください</option>
                        <option value="convenience_store">コンビニ払い</option>
                        <option value="card">クレジットカード</option>
                    </select>
                    <div class="message">
                    <p class="message-error">@error('payment_method'){{ $message }}@enderror</p>
                    </div>
                </div>
        </div>
        <div class="bottom-section">
            <div class="bottom-section__shipping">
                <h2 class="bottom-section__shipping--label">配送先</h2>
                <a class="bottom-section__shipping--update" href="{{ route('purchase.address', ['item' => $item->id]) }}">変更する</a>
            </div>
            <div class="bottom-section__destination">
                <p class="bottom-section__destination--postal-code">
                    {{ $profile?->postal_code ?? '000-0000' }}
                </p>
                <p class="bottom-section__destination--address">
                    {{ $profile?->address ?? '住所・建物名' }}
                </p>
                <p class="bottom-section__destination--building">
                    {{ $profile?->building ?? '' }}
                </p>
                <div class="message">
                    <p class="message-error">@error('shipping'){{ $message }}@enderror</p>
                </div>
            </div>
        </div>
    </div>
    <div class="buy-group">
        <table class="buy-table">
            <tr class="buy-table__tr">
                <th class="buy-table__th"><p class="buy-label">商品代金</p></th>
                <th class="buy-table__th"><p class="buy-price">{{ number_format($item->price) }}</p></th>
            </tr>
            <tr class="buy-table__tr">
                <td class="buy-table__td"><p class="buy-label">支払い方法</p></td>
                <td class="buy-table__td"><p class="buy-payment" id="buyPayment">選択してください</p></td>
            </tr>
        </table>
        <input type="hidden" name="product_id" value="{{ $item->id }}">
        <button class="buy-btn" type="submit">購入する</button>
        </form>
    </div>
</div>
<script>
    const select = document.getElementById('paymentSelect');
    const view   = document.getElementById('buyPayment');
    const sync = () => {
    view.textContent = select.value
        ? select.selectedOptions[0].textContent
        : '選択してください';
    };
    select.addEventListener('change', sync);
    sync();
</script>
@endsection


