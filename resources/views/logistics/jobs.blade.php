@extends('layouts.app')
@section('title', 'Haul board')
@section('content')
  <h1>Hauls</h1>
  <h2>Waiting for a quote</h2>
  <div class="cards">
    @forelse ($open as $trade)
      <article class="order-card">
        <strong>{{ $trade->code }} · {{ $trade->commodity->name }}</strong>
        <p>{{ tonnes($trade->quantity) }} · {{ $trade->delivery_city }}, {{ $trade->delivery_state }}</p>
        <a class="btn small" href="{{ route('trades.show', $trade) }}">Quote on the trade</a>
      </article>
    @empty
      <p>No open platform hauls.</p>
    @endforelse
  </div>
  @if ($trucks->isEmpty())
    <p class="meta">Quotes need a verified truck. Add one under Fleet and wait for the operator.</p>
  @endif
  <h2>Assigned to you</h2>
  @foreach ($mine as $trade)
    <p><a href="{{ route('trades.show', $trade) }}">{{ $trade->code }}</a> · {{ $trade->commodity->name }} · @include('partials.status', ['status' => $trade->status, 'group' => 'trade_statuses'])</p>
  @endforeach
@endsection
