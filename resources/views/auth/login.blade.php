@extends('layouts.public')
@section('title', 'Sign in')
@section('content')
  <div class="auth-wrap">
    <section class="auth-story">
      <a class="brand" href="{{ route('home') }}" style="color:#f6f1e6"><strong>AgrifiMarket</strong></a>
      <div>
        <p class="kicker">Welcome back</p>
        <h1>The book is open.</h1>
        <p>Prices, orders, hauls, and wallets live on the same desk.</p>
      </div>
      <div class="demo">
        <strong>Sample desks</strong>
        <p class="meta">Password for each is <b>password</b></p>
        <p class="meta">ada.okonkwo@agrifimarket.test · operator</p>
        <p class="meta">ibrahim.bello@agrifimarket.test · buyer</p>
        <p class="meta">amina.yusuf@agrifimarket.test · aggregator</p>
        <p class="meta">chinedu.okafor@agrifimarket.test · farmer</p>
        <p class="meta">grace.danjuma@agrifimarket.test · dealer</p>
        <p class="meta">musa.lawal@agrifimarket.test · haulier</p>
        <p class="meta">fatima.sule@agrifimarket.test · insurer</p>
      </div>
    </section>
    <section class="auth-panel">
      <h2>Sign in</h2>
      <form class="stack" method="POST" action="{{ route('login') }}">
        @csrf
        <label>Email<input type="email" name="email" value="{{ old('email') }}" required autofocus></label>
        <label>Password<input type="password" name="password" required></label>
        <label style="grid-template-columns: auto 1fr; align-items:center"><input type="checkbox" name="remember" value="1" style="width:auto"> Remember this browser</label>
        <button class="btn" type="submit">Enter</button>
      </form>
      <p class="meta">New here? <a href="{{ route('register') }}">Open an account</a></p>
    </section>
  </div>
@endsection
