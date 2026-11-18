@extends('layouts.app')
@section('title', 'Official prices')
@section('content')
  <header class="queue-head">
    <p class="kicker">Official prices</p>
    <h1>Publish a price</h1>
    <p>A figure here becomes the desk benchmark for that commodity and market.</p>
  </header>
  <form class="form-grid panel" method="POST" action="{{ route('admin.prices.store') }}">
    @csrf
    <label>Commodity<select name="commodity_id" required>@foreach($commodities as $commodity)<option value="{{ $commodity->id }}">{{ $commodity->name }}</option>@endforeach</select></label>
    <label>Market<select name="market_id" required>@foreach($markets as $market)<option value="{{ $market->id }}">{{ $market->label() }}</option>@endforeach</select></label>
    <label>₦ per kg<input type="number" step="0.01" name="price_per_kg" required></label>
    <label>Bag weight kg<input type="number" step="0.1" name="bag_weight_kg" value="100" required></label>
    <label>Date<input type="date" name="recorded_on" value="{{ now()->toDateString() }}" required></label>
    <label>Note<input name="notes"></label>
    <div class="wide"><button class="btn">Publish</button></div>
  </form>

  <section class="book">
    <div class="book-head">
      <div>
        <h2>Recent prices</h2>
        <p>{{ $prices->count() }} on the book, newest first.</p>
      </div>
      <a class="btn secondary small" href="{{ route('prices.export') }}">Export the book</a>
    </div>
    @if ($prices->isEmpty())
      <p class="queue-empty">No prices published yet.</p>
    @else
      <div class="price-book">
        @foreach ($prices as $price)
          @php
            $crop = strtolower($price->commodity->name);
            $tone = str_contains($crop, 'maize') ? 'maize'
              : (str_contains($crop, 'soy') ? 'soy'
              : (str_contains($crop, 'sesame') ? 'sesame'
              : (str_contains($crop, 'rice') || str_contains($crop, 'paddy') || str_contains($crop, 'bran') ? 'rice'
              : (str_contains($crop, 'hibiscus') ? 'hibiscus'
              : (str_contains($crop, 'millet') || str_contains($crop, 'sorghum') ? 'millet'
              : (str_contains($crop, 'groundnut') || str_contains($crop, 'nut') ? 'nut'
              : (str_contains($crop, 'bean') || str_contains($crop, 'cowpea') ? 'soy' : 'grain')))))));
            $money = function ($amount) {
                $amount = (float) $amount;

                return naira($amount, fmod($amount, 1.0) === 0.0 ? 0 : 2);
            };
            $bagKg = rtrim(rtrim(number_format((float) $price->bag_weight_kg, 1), '0'), '.');
          @endphp
          <article class="book-card tone-{{ $tone }}">
            <div class="book-crop" aria-hidden="true">@include('partials.crop', ['tone' => $tone])</div>
            <div class="book-copy">
              <strong>{{ $price->commodity->name }}</strong>
              <p>{{ $price->market->name }}, {{ $price->market->state }}</p>
              <p>{{ $price->recorded_on->format('d M Y') }}</p>
            </div>
            <div class="book-figure">
              <b>{{ $money($price->price_per_kg) }}</b>
              <span>per kg</span>
            </div>
            <p class="book-more">{{ $money($price->price_per_bag) }} for {{ $bagKg }} kg · {{ $money($price->price_per_tonne) }} a tonne @if($price->notes)<span>{{ $price->notes }}</span>@endif</p>
          </article>
        @endforeach
      </div>
    @endif
  </section>
@endsection
