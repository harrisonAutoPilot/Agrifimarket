<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'Desk') — AgrifiMarket</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,560;9..144,650&family=Outfit:wght@360;480;580&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/agrifi.css') }}">
  <link rel="icon" href="{{ asset('favicon.svg') }}">
</head>
<body>
  <input id="nav-toggle" class="nav-toggle" type="checkbox">
  <div class="shell">
    <aside>
      <a class="brand" href="{{ route('home') }}">
        <span class="mark" aria-hidden="true"><svg viewBox="0 0 32 32"><path d="M6 22c5-1 7-8 10-8s5 7 10 8" fill="none" stroke="#d4a054" stroke-width="2"/><circle cx="16" cy="11" r="2.4" fill="#f4efe6"/></svg></span>
        <span><strong>AgrifiMarket</strong><small>Desk</small></span>
      </a>
      <p class="who">{{ auth()->user()->deskName() }}<span>{{ role_label(auth()->user()->role) }}</span></p>
      <nav>
        @foreach ($navItems as $item)
          <a href="{{ route($item[1]) }}" @class(['is-active' => request()->routeIs($item[1]) || request()->routeIs(str($item[1])->before('.') . '.*')])>{{ $item[0] }}</a>
        @endforeach
      </nav>
      <form method="POST" action="{{ route('logout') }}">@csrf<button class="text-btn" style="color:#d9e4dc">Sign out</button></form>
    </aside>
    <div class="workspace">
      <header class="workspace-bar">
        <label class="burger" for="nav-toggle">Menu</label>
        <a href="{{ route('wallet.index') }}">Wallet {{ naira(auth()->user()->wallet->balance ?? 0) }}</a>
        <a href="{{ route('notifications.index') }}">Alerts @if($unreadAlerts)<b>{{ $unreadAlerts }}</b>@endif</a>
      </header>
      <main>
        @include('partials.flash')
        @yield('content')
      </main>
    </div>
  </div>
</body>
</html>
