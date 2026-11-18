@extends('layouts.app')
@section('title', 'Accept '.$order->code)
@section('content')
  <p class="kicker">{{ $order->buyer->deskName() }}</p>
  <h1>Fill {{ $order->commodity->name }}</h1>
  <p>{{ tonnes($order->remaining()) }} still open at {{ naira($order->price_per_kg) }}/kg in {{ $order->city }}, {{ $order->state }}.</p>
  <p class="meta">{{ $order->description }}</p>
  <form class="form-grid panel" method="POST" action="{{ route('demands.accept.store', $order) }}">
    @csrf
    <label>Tonnes you will supply<input type="number" step="0.01" name="quantity" value="{{ old('quantity', $order->remaining()) }}" required></label>
    <label>Your price per kg<input type="number" step="0.01" name="price_per_kg" value="{{ old('price_per_kg', $order->price_per_kg) }}" required></label>
    <label>Moisture %<input type="number" step="0.1" name="moisture" value="{{ old('moisture', $order->moisture_max) }}"></label>
    <label>Purity %<input type="number" step="0.1" name="purity" value="{{ old('purity', $order->purity_min) }}"></label>
    <label>Grade
      <select name="grade">
        @foreach (config('marketplace.grades') as $grade)
          <option @selected(old('grade', $order->grade) === $grade)>{{ $grade }}</option>
        @endforeach
      </select>
    </label>
    <label>Movement
      <select name="delivery_mode">
        <option value="platform" @selected(old('delivery_mode') === 'platform')>Ask AgrifiMarket hauliers</option>
        <option value="self" @selected(old('delivery_mode') === 'self')>I will deliver</option>
      </select>
    </label>
    <label>Self-delivery cost (₦)<input type="number" step="0.01" name="logistics_cost" value="{{ old('logistics_cost', 0) }}"></label>
    <label>Pickup state
      <select name="state" required>
        @foreach (config('marketplace.states') as $state)
          <option @selected(old('state', auth()->user()->state) === $state)>{{ $state }}</option>
        @endforeach
      </select>
    </label>
    <label>Pickup city<input name="city" value="{{ old('city', auth()->user()->city) }}" required></label>
    <label class="wide">Pickup address<input name="address" value="{{ old('address', auth()->user()->address) }}"></label>
    <div class="wide"><button class="btn">Agree this trade</button></div>
  </form>
@endsection
