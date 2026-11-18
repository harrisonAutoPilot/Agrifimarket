<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'AgrifiMarket') — AgrifiMarket</title>
  <meta name="description" content="AgrifiMarket is a Nigerian agricultural commodity desk for farmers, aggregators, buyers, hauliers, and insurers.">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,560;9..144,650&family=Outfit:wght@360;480;580&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/agrifi.css') }}">
  <link rel="icon" href="{{ asset('favicon.svg') }}">
</head>
<body @class(['page-home' => request()->routeIs('home')])>
  <input id="site-menu" class="nav-toggle" type="checkbox">
  <div class="ticker" aria-label="Latest official prices">
    @forelse ($ticker as $price)
      <span>{{ $price->commodity->name }} · {{ $price->market->name }} <b>{{ naira($price->price_per_kg, 0) }}/kg</b></span>
    @empty
      <span>Official prices publish here each market day.</span>
    @endforelse
  </div>
  <header class="site-header">
    <a class="brand" href="{{ route('home') }}">
      <span class="mark" aria-hidden="true"><svg viewBox="0 0 32 32"><path d="M6 22c5-1 7-8 10-8s5 7 10 8" fill="none" stroke="#d4a054" stroke-width="2"/><circle cx="16" cy="11" r="2.4" fill="#f4efe6"/></svg></span>
      <span><strong>AgrifiMarket</strong><small>Commodity desk</small></span>
    </a>
    <label class="burger" for="site-menu">Menu</label>
    <nav class="site-nav">
      <a href="{{ route('prices.index') }}" @class(['is-active' => request()->routeIs('prices.*')])>Prices</a>
      <a href="{{ route('orders.index') }}" @class(['is-active' => request()->routeIs('orders.*')])>Orders</a>
      <a href="{{ route('marketplace.index') }}">Inputs</a>
      <a href="{{ route('hubs') }}">Hubs</a>
      <a href="{{ route('support') }}">Support</a>
    </nav>
    <div class="header-actions">
      @auth
        <a class="btn small" href="{{ route(auth()->user()->isAdmin() ? 'admin.home' : 'dashboard') }}">Open desk</a>
      @else
        <a href="{{ route('login') }}">Sign in</a>
        <a class="btn small" href="{{ route('register') }}">Open an account</a>
      @endauth
    </div>
  </header>
  <main>
    @include('partials.flash')
    @yield('content')
  </main>
  <footer class="site-footer">
    <div>
      <a class="brand" href="{{ route('home') }}"><strong>AgrifiMarket</strong></a>
      <p class="meta">A commodity desk for Nigerian grains, pulses, and oilseeds. Operated for Voriancorelli Limited.</p>
    </div>
    <div>
      <strong>Desk</strong>
      <a href="{{ route('prices.index') }}">Official prices</a>
      <a href="{{ route('orders.index') }}">Open buy orders</a>
      <a href="{{ route('marketplace.index') }}">Farm inputs</a>
      <a href="{{ route('hubs') }}">Collection hubs</a>
    </div>
    <div>
      <strong>Contact</strong>
      <a href="mailto:{{ config('marketplace.contact_email') }}">{{ config('marketplace.contact_email') }}</a>
      <span class="meta">{{ config('marketplace.contact_phone') }}</span>
      <span class="meta">{{ config('marketplace.contact_city') }}</span>
      <a href="{{ route('legal', 'terms') }}">Terms</a>
      <a href="{{ route('legal', 'privacy') }}">Privacy</a>
    </div>
  </footer>
</body>
</html>
