@extends('layouts.app')
@section('title', $order->exists ? 'Edit order' : 'Post order')
@section('content')
  <h1>{{ $order->exists ? 'Edit '.$order->code : 'Post a buy order' }}</h1>
  <form class="form-grid panel" method="POST" action="{{ $order->exists ? route('demands.update', $order) : route('demands.store') }}">
    @csrf
    @if ($order->exists) @method('PUT') @endif
    <label>Commodity
      <select name="commodity_id" required>
        @foreach ($commodities as $commodity)
          <option value="{{ $commodity->id }}" @selected(old('commodity_id', $order->commodity_id) == $commodity->id)>{{ $commodity->name }}</option>
        @endforeach
      </select>
    </label>
    <label>Tonnes<input type="number" step="0.01" name="quantity" value="{{ old('quantity', $order->quantity) }}" required></label>
    <label>Bid per kg<input type="number" step="0.01" name="price_per_kg" value="{{ old('price_per_kg', $order->price_per_kg) }}" required></label>
    <label>Payment window (days)<input type="number" name="payment_days" value="{{ old('payment_days', $order->payment_days) }}" required></label>
    <label>Moisture max %<input type="number" step="0.1" name="moisture_max" value="{{ old('moisture_max', $order->moisture_max) }}"></label>
    <label>Purity min %<input type="number" step="0.1" name="purity_min" value="{{ old('purity_min', $order->purity_min) }}"></label>
    <label>Grade
      <select name="grade">
        @foreach (config('marketplace.grades') as $grade)
          <option @selected(old('grade', $order->grade) === $grade)>{{ $grade }}</option>
        @endforeach
      </select>
    </label>
    <label>Delivery
      <select name="delivery_type">
        @foreach (config('marketplace.delivery_types') as $value => $label)
          <option value="{{ $value }}" @selected(old('delivery_type', $order->delivery_type) === $value)>{{ $label }}</option>
        @endforeach
      </select>
    </label>
    <label>State
      <select name="state" required>
        @foreach (config('marketplace.states') as $state)
          <option @selected(old('state', $order->state) === $state)>{{ $state }}</option>
        @endforeach
      </select>
    </label>
    <label>City<input name="city" value="{{ old('city', $order->city) }}" required></label>
    <label class="wide">Address<input name="address" value="{{ old('address', $order->address) }}"></label>
    <label>Opens<input type="date" name="starts_on" value="{{ old('starts_on', optional($order->starts_on)->toDateString()) }}" required></label>
    <label>Closes<input type="date" name="ends_on" value="{{ old('ends_on', optional($order->ends_on)->toDateString()) }}" required></label>
    <label>Visibility
      <select name="status">
        <option value="active" @selected(old('status', $order->status) === 'active')>Active</option>
        <option value="draft" @selected(old('status', $order->status) === 'draft')>Draft</option>
      </select>
    </label>
    <label class="wide">Specification<textarea name="description">{{ old('description', $order->description) }}</textarea></label>
    <div class="wide"><button class="btn">Save order</button></div>
  </form>
@endsection
