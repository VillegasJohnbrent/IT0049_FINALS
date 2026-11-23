<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'POS') · {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="{{ asset('js/app.js') }}" defer></script>
</head>
<body>
    <a class="skip-link" href="#main">Skip to content</a>
    <header class="site-header">
        <div class="header-inner">
            <a class="brand" href="{{ route('sales.create') }}">{{ config('app.name') }}</a>
            @auth
                <div class="account">
                    @if(auth()->user()->avatar)
                        <img class="avatar small" src="{{ Storage::disk('public')->url(auth()->user()->avatar) }}" alt="">
                    @endif
                    <span>{{ auth()->user()->full_name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="button light small" type="submit">Log out</button>
                    </form>
                </div>
            @endauth
        </div>
        @auth
            <nav class="nav-inner" aria-label="Main navigation">
                @foreach(['sales.create' => 'Record Sale', 'sales.index' => 'Sales History', 'products.index' => 'Products', 'customers.index' => 'Customers', 'users.index' => 'Staff'] as $route => $label)
                    @php($active = str_starts_with($route, 'sales.') ? request()->routeIs($route) : request()->routeIs(strtok($route, '.').'.*'))
                    <a href="{{ route($route) }}" @class(['active' => $active]) @if($active) aria-current="page" @endif>{{ $label }}</a>
                @endforeach
            </nav>
        @endauth
    </header>
    <main id="main" class="container">
        @if(session('success'))
            <div class="alert success" role="status">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert error" role="alert" tabindex="-1" id="form-errors">
                <strong>Please check the following:</strong>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @yield('content')
    </main>
    <footer>Staff point of sale</footer>
</body>
</html>
