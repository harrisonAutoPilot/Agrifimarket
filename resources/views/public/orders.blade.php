@extends('layouts.public')
@section('title', 'Open orders')
@section('content')
  <section class="section">
    <div class="page-head">
      <div>
        <p class="kicker">Buy side</p>
        <h1>Orders waiting for grain.</h1>
      </div>
    </div>
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
      @forelse ($orders as $order)
        <article class="order-card">
          <div class="split">
            <strong>{{ $order->commodity->name }}</strong>
            @include('partials.status', ['status' => $order->status])
          </div>
          <p class="money">{{ naira($order->price_per_kg) }}/kg · {{ tonnes($order->remaining()) }} open</p>
          <p class="meta">{{ $order->city }}, {{ $order->state }} · {{ config('marketplace.delivery_types.'.$order->delivery_type) }}</p>
          <p class="meta">Moisture max {{ $order->moisture_max ?? '—' }}% · purity min {{ $order->purity_min ?? '—' }}% · {{ $order->grade }}</p>
          <p>{{ $order->description }}</p>
          <p class="meta">{{ $order->buyer->deskName() }} · closes {{ $order->ends_on?->format('d M Y') }}</p>
          @auth
            @if (in_array(auth()->user()->role, ['farmer', 'aggregator']) && auth()->user()->isApproved())
              <a class="btn small" href="{{ route('demands.accept', $order) }}">Fill this order</a>
            @endif
          @else
            <a class="btn small" href="{{ route('login') }}">Sign in to fill</a>
          @endauth
        </article>
      @empty
        <p>No open orders match that filter.</p>
      @endforelse
    </div>
    {{ $orders->links() }}
  </section>
@endsection
