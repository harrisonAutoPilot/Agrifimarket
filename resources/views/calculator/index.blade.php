@extends('layouts.app')
@section('title', 'Calculator')
@section('content')
  <header class="queue-head">
    <p class="kicker">Desk tools</p>
    <h1>Landed cost</h1>
    <p>Quantity times price, then haulage, cover, the desk fee, and commission. Blank haulage or cover uses the desk estimate.</p>
  </header>
  <form class="form-grid panel" method="POST">
    @csrf
    <label>Commodity
      <select name="commodity_id">
        <option value="">—</option>
        @foreach ($commodities as $commodity)
          <option value="{{ $commodity->id }}" @selected(old('commodity_id', request('commodity_id')) == $commodity->id)>{{ $commodity->name }}</option>
        @endforeach
      </select>
    </label>
    <label>Attach to trade
      <select name="trade_id">
        <option value="">None</option>
        @foreach ($trades as $trade)
          <option value="{{ $trade->id }}">{{ $trade->code }}</option>
        @endforeach
      </select>
    </label>
    <label>Tonnes<input type="number" step="0.01" name="quantity" value="{{ old('quantity', request('quantity', 20)) }}" required></label>
    <label>₦ per kg<input type="number" step="0.01" name="price_per_kg" value="{{ old('price_per_kg', request('price_per_kg', 450)) }}" required></label>
    <label>Grade factor<input type="number" step="0.01" name="grade_factor" value="{{ old('grade_factor', 1) }}"></label>
    <label>Haulage ₦<input type="number" step="0.01" name="logistics_cost" value="{{ old('logistics_cost') }}" placeholder="Leave blank to estimate"></label>
    <label>Cover ₦<input type="number" step="0.01" name="insurance_premium" value="{{ old('insurance_premium') }}" placeholder="Leave blank for 0.4%"></label>
    <label class="wide">Note<input name="note" value="{{ old('note') }}"></label>
    <label class="wide" style="grid-template-columns:auto 1fr; align-items:center"><input type="checkbox" name="save" value="1" style="width:auto"> Keep this working on the desk</label>
    <div class="wide"><button class="btn">Calculate</button></div>
  </form>
  @if ($result)
    <section class="book">
      <div class="book-head">
        <div>
          <h2>This working</h2>
          <p>Haulage {{ naira($result['logistics_cost']) }} · cover {{ naira($result['insurance_premium']) }} · desk fee {{ naira($result['fees']) }}@if(!empty($result['saved'])) · kept on the desk @endif</p>
        </div>
      </div>
      <div class="trade-figures">
        <article><span>Trade value</span><b>{{ naira($result['trade_value']) }}</b></article>
        <article><span>Supplier net</span><b>{{ naira($result['net_proceeds']) }}</b></article>
        <article><span>Commission</span><b>{{ naira($result['commission']) }}</b></article>
        <article><span>Buyer landed cost</span><b>{{ naira($result['landed_cost']) }}</b></article>
      </div>
    </section>
  @endif

  <section class="book">
    <div class="book-head">
      <div>
        <h2>Saved workings</h2>
        <p>{{ $snapshots->count() === 0 ? 'Nothing kept on this desk yet.' : $snapshots->count().' kept on this desk.' }}</p>
      </div>
    </div>
    @if ($snapshots->isEmpty())
      <article class="working-empty">
        <strong>No workings saved.</strong>
        <p>Calculate a lot, and tick “Keep this working on the desk” if you want it to stay here.</p>
      </article>
    @else
      <div class="working-grid">
        @foreach ($snapshots as $snapshot)
          <article class="working">
            <div>
              <strong>{{ $snapshot->commodity->name ?? 'Commodity' }}</strong>
              <p class="working-date">{{ $snapshot->created_at->timezone(config('app.timezone'))->format('d M Y') }}</p>
            </div>
            <div class="working-figure">
              <b>{{ naira($snapshot->landed_cost) }}</b>
              <span>Landed cost</span>
            </div>
            <p>{{ tonnes($snapshot->quantity) }} · {{ naira($snapshot->price_per_kg) }}/kg · supplier net {{ naira($snapshot->net_proceeds) }}@if($snapshot->note) · {{ $snapshot->note }}@endif</p>
          </article>
        @endforeach
      </div>
    @endif
  </section>
@endsection
