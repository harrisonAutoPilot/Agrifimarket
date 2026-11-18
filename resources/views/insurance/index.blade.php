@extends('layouts.app')
@section('title', 'Cover')
@section('content')
  <h1>Insurance desk</h1>
  <div class="two" style="grid-template-columns: 0.8fr 1.2fr">
    <form class="panel stack" method="POST" action="{{ route('insurance.products') }}">
      @csrf
      <h2>New product</h2>
      <label>Name<input name="name" required></label>
      <label>Type
        <select name="type">
          <option value="cargo">Cargo</option>
          <option value="trade_credit">Trade credit</option>
          <option value="warehouse">Warehouse</option>
        </select>
      </label>
      <label>Rate %<input type="number" step="0.001" name="rate_percent" required></label>
      <label>Description<textarea name="description"></textarea></label>
      <button class="btn">Save product</button>
    </form>
    <div>
      <h2>Your products</h2>
      @forelse ($products as $product)
        <p>{{ $product->name }} · {{ $product->type }} · {{ $product->rate_percent }}%</p>
      @empty
        <p class="meta">Add a rate before you quote a trade.</p>
      @endforelse
      <h2>Trades that can take cover</h2>
      @foreach ($trades as $trade)
        <p><a href="{{ route('trades.show', $trade) }}">{{ $trade->code }}</a> · {{ $trade->commodity->name }} · value {{ naira($trade->commodity_value) }}</p>
      @endforeach
    </div>
  </div>
@endsection
