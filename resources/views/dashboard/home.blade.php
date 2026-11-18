@extends('layouts.app')
@section('title', 'Overview')
@section('content')
  <div class="page-head">
    <div>
      <p class="kicker">{{ role_label(auth()->user()->role) }}</p>
      <h1>Good day, {{ auth()->user()->name }}.</h1>
    </div>
  </div>
  <div class="stats">
    <article class="stat"><b>{{ naira(auth()->user()->wallet->balance ?? 0, 0) }}</b><span class="meta">Wallet</span></article>
    <article class="stat"><b>{{ $matches }}</b><span class="meta">Open matches</span></article>
    <article class="stat"><b>{{ $activeSupplies }}</b><span class="meta">Your live supply</span></article>
    <article class="stat"><b>{{ auth()->user()->role === 'buyer' ? $myDemands : $activeDemands }}</b><span class="meta">{{ auth()->user()->role === 'buyer' ? 'Your open orders' : 'Orders on the board' }}</span></article>
  </div>
  <div class="two" style="grid-template-columns: 1.3fr 0.7fr; margin-top:1rem">
    <section class="panel">
      <h2>Recent trades</h2>
      @forelse ($trades as $trade)
        <p class="split"><a href="{{ route('trades.show', $trade) }}">{{ $trade->code }} · {{ $trade->commodity->name }}</a> @include('partials.status', ['status' => $trade->status, 'group' => 'trade_statuses'])</p>
      @empty
        <p class="meta">No trades on your desk yet.</p>
      @endforelse
    </section>
    <section class="panel">
      <h2>Latest official prices</h2>
      @foreach ($prices as $price)
        <p class="split"><span>{{ $price->commodity->name }}<br><span class="meta">{{ $price->market->name }}</span></span><b>{{ naira($price->price_per_kg, 0) }}</b></p>
      @endforeach
      <a href="{{ route('prices.index') }}">Full price book</a>
    </section>
  </div>
@endsection
