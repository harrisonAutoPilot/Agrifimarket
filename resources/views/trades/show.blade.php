@extends('layouts.app')
@section('title', $trade->code)
@section('content')
  @php($user = auth()->user())
  @php($showNext = ($trade->status === 'trade_agreed' && ($user->id === $trade->buyer_id || $user->isAdmin()))
      || (in_array($trade->status, ['contract_issued']) && $trade->delivery_mode === 'self' && ($user->id === $trade->supplier_id || $user->isAdmin()))
      || ($trade->status === 'logistics_assigned' && ($user->id === $trade->logistics_user_id || $user->isAdmin()))
      || ($trade->status === 'in_transit' && (($trade->delivery_mode === 'self' && $user->id === $trade->supplier_id) || $user->id === $trade->logistics_user_id || $user->isAdmin()))
      || ($trade->status === 'delivered' && ($user->id === $trade->buyer_id || $user->isAdmin()))
      || ($trade->status === 'quality_verified' && ($user->id === $trade->buyer_id || $user->isAdmin()))
      || ($trade->status === 'payment_settled' && ($user->id === $trade->buyer_id || $user->isAdmin()))
      || ($user->role === 'logistics' && $trade->status === 'contract_issued' && $trade->delivery_mode === 'platform' && ! $trade->logistics_user_id)
      || $trade->haulQuotes->isNotEmpty()
      || ($user->role === 'insurer' && in_array($trade->status, ['contract_issued', 'logistics_assigned', 'in_transit']) && ! $trade->insurer_user_id)
      || $trade->insuranceQuotes->isNotEmpty()
      || (! in_array($trade->status, ['closed', 'disputed']) && $trade->involves($user)))
  <div class="page-head">
    <div>
      <p class="kicker">{{ $trade->commodity->name }} · {{ tonnes($trade->quantity) }}</p>
      <h1>{{ $trade->code }}</h1>
    </div>
    <div class="trade-tools">
      @include('partials.status', ['status' => $trade->status, 'group' => 'trade_statuses'])
      <a class="btn secondary small" href="{{ route('trades.statement', $trade) }}">Statement</a>
    </div>
  </div>
  <div class="trade-figures">
    <article><span>Commodity value</span><b>{{ naira($trade->commodity_value) }}</b></article>
    <article><span>Haulage</span><b>{{ naira($trade->logistics_cost) }}</b></article>
    <article><span>Cover</span><b>{{ naira($trade->insurance_premium) }}</b></article>
    <article><span>Buyer pays</span><b>{{ naira($trade->buyer_total) }}</b></article>
  </div>
  <div class="trade-file">
    <section class="trade-panel">
      <h2>Parties</h2>
      <div class="party-list">
        <article class="party party-buyer">
          <span class="party-mark" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M6 9h12l-1 10H7z"/><path d="M9 9V7.5a3 3 0 0 1 6 0V9"/></svg></span>
          <div><span class="party-role">Buyer</span><strong>{{ $trade->buyer->deskName() }}</strong></div>
        </article>
        <article class="party party-supplier">
          <span class="party-mark" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 20V9"/><path d="M12 13c-2.4-1.2-4.6-.4-6 1.2 0-4.2 2.2-6.6 6-7.6 3.8 1 6 3.4 6 7.6-1.4-1.6-3.6-2.4-6-1.2z"/><path d="M8 20h8"/></svg></span>
          <div><span class="party-role">Supplier</span><strong>{{ $trade->supplier->deskName() }}</strong></div>
        </article>
        <article class="party party-haulier">
          <span class="party-mark" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M3 7.5h11v8H3zM14 11h4.2L21 14.2V15.5h-7"/><circle cx="7" cy="17.2" r="1.5"/><circle cx="17.2" cy="17.2" r="1.5"/></svg></span>
          <div>
            <span class="party-role">Haulier</span>
            <strong>{{ $trade->haulier?->deskName() ?? 'Not assigned' }}</strong>
            <small>{{ $trade->delivery_mode === 'self' ? 'Supplier is moving this lot' : 'Platform haul' }}</small>
          </div>
        </article>
        <article class="party party-insurer">
          <span class="party-mark" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 3.5 5 6.2v5.3c0 4.2 2.8 7.2 7 8.8 4.2-1.6 7-4.6 7-8.8V6.2z"/></svg></span>
          <div><span class="party-role">Insurer</span><strong>{{ $trade->insurer?->deskName() ?? 'No cover yet' }}</strong></div>
        </article>
      </div>
      <dl class="trade-facts">
        <div><dt>Price</dt><dd>{{ naira($trade->price_per_kg) }}/kg</dd></div>
        <div><dt>Grade</dt><dd>{{ $trade->grade }}</dd></div>
        <div><dt>Moisture</dt><dd>{{ $trade->moisture ?? '—' }}%</dd></div>
        <div><dt>Purity</dt><dd>{{ $trade->purity ?? '—' }}%</dd></div>
        <div class="wide"><dt>Deliver to</dt><dd>{{ $trade->delivery_address }} · {{ $trade->delivery_city }}, {{ $trade->delivery_state }}</dd></div>
        <div><dt>Supplier net</dt><dd>{{ naira($trade->supplier_net) }}</dd></div>
        <div><dt>Commission</dt><dd>{{ naira($trade->commission) }}</dd></div>
        <div><dt>Desk fee</dt><dd>{{ naira($trade->platform_fee) }}</dd></div>
        @if ($trade->invoice)
          <div><dt>Invoice</dt><dd>{{ $trade->invoice->number }} · {{ naira($trade->invoice->amount) }}</dd></div>
        @endif
      </dl>
    </section>
    <section class="trade-panel">
      <h2>History</h2>
      <ol class="trade-timeline">
        @foreach ($trade->events as $event)
          <li>
            <span class="trade-dot"></span>
            <div>
              <strong>{{ status_label('trade_statuses', $event->status) }}</strong>
              <p class="meta">{{ $event->created_at->timezone(config('app.timezone'))->format('d M Y H:i') }} · {{ $event->user?->deskName() }}</p>
              @if ($event->note)<p>{{ $event->note }}</p>@endif
            </div>
          </li>
        @endforeach
      </ol>
    </section>
  </div>

  @if ($showNext)
  <section class="trade-panel no-print">
    <h2>Next step</h2>
    @if ($trade->status === 'trade_agreed' && ($user->id === $trade->buyer_id || $user->isAdmin()))
      <form method="POST" action="{{ route('trades.contract', $trade) }}">@csrf<button class="btn">Issue contract</button></form>
    @endif
    @if (in_array($trade->status, ['contract_issued']) && $trade->delivery_mode === 'self' && ($user->id === $trade->supplier_id || $user->isAdmin()))
      <form method="POST" action="{{ route('trades.transition', $trade) }}">@csrf<input type="hidden" name="status" value="in_transit"><button class="btn">Mark in transit</button></form>
    @endif
    @if ($trade->status === 'logistics_assigned' && ($user->id === $trade->logistics_user_id || $user->isAdmin()))
      <form method="POST" action="{{ route('trades.transition', $trade) }}">@csrf<input type="hidden" name="status" value="in_transit"><button class="btn">Mark in transit</button></form>
    @endif
    @if ($trade->status === 'in_transit' && (($trade->delivery_mode === 'self' && $user->id === $trade->supplier_id) || $user->id === $trade->logistics_user_id || $user->isAdmin()))
      <form method="POST" action="{{ route('trades.transition', $trade) }}">@csrf<input type="hidden" name="status" value="delivered"><label>Note<input name="note" placeholder="Arrival note"></label><button class="btn">Mark delivered</button></form>
    @endif
    @if ($trade->status === 'delivered' && ($user->id === $trade->buyer_id || $user->isAdmin()))
      <form method="POST" action="{{ route('trades.transition', $trade) }}">@csrf<input type="hidden" name="status" value="quality_verified"><label>Quality note<input name="note" placeholder="Grade confirmed on arrival"></label><button class="btn">Confirm quality</button></form>
    @endif
    @if ($trade->status === 'quality_verified' && ($user->id === $trade->buyer_id || $user->isAdmin()))
      <form method="POST" action="{{ route('trades.pay', $trade) }}">@csrf<button class="btn gold">Pay {{ naira($trade->buyer_total) }} from wallet</button></form>
    @endif
    @if ($trade->status === 'payment_settled' && ($user->id === $trade->buyer_id || $user->isAdmin()))
      <form method="POST" action="{{ route('trades.transition', $trade) }}">@csrf<input type="hidden" name="status" value="closed"><button class="btn">Close trade</button></form>
    @endif

    @if ($user->role === 'logistics' && $trade->status === 'contract_issued' && $trade->delivery_mode === 'platform' && ! $trade->logistics_user_id)
      <form class="form-grid" method="POST" action="{{ route('logistics.quote', $trade) }}">
        @csrf
        <label>Verified truck
          <select name="truck_id" required>
            @foreach ($user->trucks()->where('status', 'verified')->get() as $truck)
              <option value="{{ $truck->id }}">{{ $truck->registration_number }} · {{ $truck->capacity_tonnes }}t</option>
            @endforeach
          </select>
        </label>
        <label>₦ per tonne<input type="number" step="0.01" name="price_per_tonne" required></label>
        <label class="wide">Note<input name="note"></label>
        <button class="btn">Send haul quote</button>
      </form>
    @endif

    @if ($trade->haulQuotes->isNotEmpty())
      <h3>Haul quotes</h3>
      @foreach ($trade->haulQuotes as $quote)
        <p class="split quote-line">{{ $quote->partner->deskName() }} · {{ $quote->truck?->registration_number }} · {{ naira($quote->total) }} @include('partials.status', ['status' => $quote->status, 'group' => 'user_statuses'])
          @if ($quote->status === 'submitted' && ($user->id === $trade->buyer_id || $user->isAdmin()) && $trade->status === 'contract_issued')
            <form method="POST" action="{{ route('trades.haul', [$trade, $quote]) }}">@csrf<button class="btn small">Award haul</button></form>
          @endif
        </p>
      @endforeach
    @endif

    @if ($user->role === 'insurer' && in_array($trade->status, ['contract_issued', 'logistics_assigned', 'in_transit']) && ! $trade->insurer_user_id)
      <form method="POST" action="{{ route('insurance.quote', $trade) }}" class="form-grid">
        @csrf
        <label>Product
          <select name="insurance_product_id" required>
            @foreach ($user->insuranceProducts()->where('active', true)->get() as $product)
              <option value="{{ $product->id }}">{{ $product->name }} · {{ $product->rate_percent }}%</option>
            @endforeach
          </select>
        </label>
        <label>Cover note<input name="cover_note"></label>
        <button class="btn">Quote premium</button>
      </form>
    @endif

    @if ($trade->insuranceQuotes->isNotEmpty())
      <h3>Cover quotes</h3>
      @foreach ($trade->insuranceQuotes as $quote)
        <p class="split quote-line">{{ $quote->partner->deskName() }} · {{ naira($quote->premium) }} · {{ $quote->policy_number }} @include('partials.status', ['status' => $quote->status, 'group' => 'user_statuses'])
          @if ($quote->status === 'quoted' && ($user->id === $trade->buyer_id || $user->isAdmin()))
            <form method="POST" action="{{ route('trades.cover', [$trade, $quote]) }}">@csrf<button class="btn small">Accept cover</button></form>
          @endif
        </p>
      @endforeach
    @endif

    @if (! in_array($trade->status, ['closed', 'disputed']) && $trade->involves($user))
      <form method="POST" action="{{ route('trades.dispute', $trade) }}" class="stack">
        @csrf
        <label>Raise a dispute<textarea name="reason" required></textarea></label>
        <button class="btn quiet">Flag dispute</button>
      </form>
    @endif
  </section>
  @endif
@endsection
