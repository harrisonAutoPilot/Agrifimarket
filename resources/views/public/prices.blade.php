@extends('layouts.public')
@section('title', 'Official prices')
@section('content')
  <section class="section">
    <div class="page-head">
      <div>
        <p class="kicker">Official price book</p>
        <h1>What the market paid.</h1>
        <p class="meta">Published by the AgrifiMarket desk. Listings can use these figures as the benchmark.</p>
      </div>
      <a class="btn secondary" href="{{ route('prices.export') }}">Export CSV</a>
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
      <label>Market
        <select name="market">
          <option value="">All</option>
          @foreach ($markets as $market)
            <option value="{{ $market->id }}" @selected(request('market') == $market->id)>{{ $market->label() }}</option>
          @endforeach
        </select>
      </label>
      <label>Month
        <input type="month" name="month" value="{{ request('month') }}">
      </label>
      <label>Show price
        <select name="unit">
          @foreach (['kg' => 'Per kg', 'bag' => 'Per bag', 'tonne' => 'Per tonne'] as $key => $label)
            <option value="{{ $key }}" @selected($unit === $key)>{{ $label }}</option>
          @endforeach
        </select>
      </label>
      <button class="btn small" type="submit">Filter</button>
    </form>

    @if ($focus)
      <article class="panel" style="margin-bottom:1rem">
        <h2>{{ $focus->name }}</h2>
        <p>{{ $focus->description }}</p>
        <p class="meta">Primary markets: {{ $focus->primary_markets }} · {{ $focus->season_note }}</p>
      </article>
    @endif

    <div class="table-wrap">
      <table>
        <thead>
          <tr><th>Date</th><th>Commodity</th><th>Market</th><th>State</th><th>Price</th><th>Bag kg</th></tr>
        </thead>
        <tbody>
          @forelse ($prices as $price)
            <tr>
              <td>{{ $price->recorded_on->format('d M Y') }}</td>
              <td>{{ $price->commodity->name }}</td>
              <td>{{ $price->market->name }}</td>
              <td>{{ $price->market->state }}</td>
              <td class="money">{{ naira($price->display($unit)) }}</td>
              <td>{{ $price->bag_weight_kg }}</td>
            </tr>
          @empty
            <tr><td colspan="6">No prices for that filter.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    {{ $prices->links() }}

    <h2 style="margin-top:2rem">Range this period</h2>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Commodity</th><th>Low /kg</th><th>Average /kg</th><th>High /kg</th></tr></thead>
        <tbody>
          @foreach ($summary as $row)
            <tr>
              <td>{{ $row->name }}</td>
              <td>{{ naira($row->min_price) }}</td>
              <td>{{ naira($row->avg_price) }}</td>
              <td>{{ naira($row->max_price) }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </section>
@endsection
