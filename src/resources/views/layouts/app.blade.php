<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COACHTECH furima</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/common.css') }}" />
    @yield('css')
</head>
<body>
    <div>
        <header class="header">
            <h1 class="logo-block">
                <a href="{{ url('/') }}">
                <img class="logo-img" src="{{ asset('images/logo.svg') }}" alt="COACHTECH">
                </a>
            </h1>
            @yield('header_nav')
        </header>
        <div>
            @yield('content')
        </div>
    </div>
</body>
</html>
