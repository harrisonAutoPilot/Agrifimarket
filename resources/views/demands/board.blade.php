@extends('layouts.app')
@section('title', 'Orders to fill')
@section('content')
  <h1>Orders you can fill</h1>
  <form class="filters" method="GET">
    <label>Commodity
      <select name="commodity">
        <option value="">All</option>
        @foreach ($commodities as $commodity)
          <option value="{{ $commodity->id }}" @selected(request('commodity') == $commodity->id)>{{ $commodity->name }}</option>
        @endforeach
      </select>
    </label>
    <label>State
      <select name="state">
        <option value="">All</option>
        @foreach (config('marketplace.states') as $state)
          <option @selected(request('state') === $state)>{{ $state }}</option>
        @endforeach
      </select>
    </label>
    <button class="btn small">Filter</button>
  </form>
  <div class="cards">
    @foreach ($orders as $order)
      <article class="order-card">
        <strong>{{ $order->code }} · {{ $order->commodity->name }}</strong>
        <p>{{ tonnes($order->remaining()) }} at {{ naira($order->price_per_kg) }}/kg</p>
        <p class="meta">{{ $order->city }}, {{ $order->state }} · {{ $order->buyer->deskName() }}</p>
        <a class="btn small" href="{{ route('demands.accept', $order) }}">Accept</a>
      </article>
    @endforeach
  </div>
  {{ $orders->links() }}
@endsection
