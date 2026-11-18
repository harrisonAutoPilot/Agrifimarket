@extends('layouts.public')
@section('title', 'Where the harvest finds a buyer')
@section('content')
  <section class="hero">
    <div>
      <p class="kicker">Nigerian agricultural commodities</p>
      <h1>Sell the harvest. Buy the grade, not the guess.</h1>
      <p class="lede">AgrifiMarket brings farmers, aggregators, processors, hauliers, and insurers onto one desk. Official prices, open orders, and a trade file that runs from agreement to payment.</p>
      <div class="hero-actions">
        <a class="btn" href="{{ route('orders.index') }}">See open orders</a>
        <a class="btn secondary" href="{{ route('prices.index') }}">Today’s prices</a>
      </div>
    </div>
    @if ($orders->isEmpty())
      <article class="order-card"><p>Buy orders will appear here as soon as a processor posts one.</p></article>
    @else
      <div class="order-stage" data-order-stage>
        <div class="order-carousel">
          <button class="order-nav order-nav-prev" type="button" data-order-prev aria-label="Previous order">
            <svg viewBox="0 0 16 16" width="16" height="16" aria-hidden="true"><path d="M10 3 5 8l5 5" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </button>
          <div class="order-cards">
            @foreach ($orders as $index => $order)
              @php
                $count = $orders->count();
                $pos = $index === 0 ? 'active' : ($index === 1 ? 'next' : ($index === $count - 1 ? 'prev' : 'hidden'));
                $crop = strtolower($order->commodity->name);
                $tone = str_contains($crop, 'maize') ? 'maize'
                  : (str_contains($crop, 'soy') ? 'soy'
                  : (str_contains($crop, 'sesame') ? 'sesame'
                  : (str_contains($crop, 'rice') ? 'rice'
                  : (str_contains($crop, 'hibiscus') ? 'hibiscus'
                  : (str_contains($crop, 'millet') ? 'millet' : ['maize', 'soy', 'sesame', 'rice', 'hibiscus'][$index % 5])))));
              @endphp
              <article class="order-card tone-{{ $tone }}" data-order-card data-pos="{{ $pos }}">
                <div class="order-crop" aria-hidden="true">@include('partials.crop', ['tone' => $tone])</div>
                <div class="order-copy">
                  <div class="split">
                    <strong>{{ $order->commodity->name }}</strong>
                    <span class="order-price">{{ naira($order->price_per_kg, 0) }}/kg</span>
                  </div>
                  <p class="meta">{{ tonnes($order->remaining()) }} still open · {{ $order->state }}</p>
                  <p class="meta">Moisture max {{ $order->moisture_max }}% · {{ $order->buyer->deskName() }}</p>
                </div>
              </article>
            @endforeach
          </div>
          <button class="order-nav order-nav-next" type="button" data-order-next aria-label="Next order">
            <svg viewBox="0 0 16 16" width="16" height="16" aria-hidden="true"><path d="M6 3l5 5-5 5" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </button>
        </div>
        <div class="order-progress" data-order-progress aria-label="Open orders">
          <span class="order-progress-fill"></span>
          @foreach ($orders as $order)
            <button class="order-dot{{ $loop->first ? ' is-active' : '' }}" type="button" data-order-dot aria-label="{{ $order->commodity->name }}" @if($loop->first) aria-current="true" @endif></button>
          @endforeach
        </div>
      </div>
    @endif
  </section>

  <section class="section home-figures">
    <div class="home-stats">
      <article>
        <b>{{ $commodityCount }}</b>
        <span>Commodities on the book</span>
      </article>
      <article>
        <b>{{ $marketCount }}</b>
        <span>Markets with official prices</span>
      </article>
      <article>
        <b>{{ $tradeCount }}</b>
        <span>Trades settled</span>
      </article>
      <article>
        <b>{{ naira($settled, 0) }}</b>
        <span>Commodity value settled</span>
      </article>
    </div>
  </section>

  <section class="section alt journey">
    <div class="journey-head">
      <p class="kicker">How a trade moves</p>
      <h2>From a posted order to money in the wallet.</h2>
    </div>
    <ol class="steps">
      <li>
        <span class="step-no">1</span>
        <span class="step-icon" aria-hidden="true"><svg viewBox="0 0 32 32"><circle cx="16" cy="16" r="11" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M10.5 16.2 14.2 20l7.4-8" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
        <h3>Get approved</h3>
        <p>Choose a role, add your business details, and upload identity documents. An operator opens the desk.</p>
      </li>
      <li>
        <span class="step-no">2</span>
        <span class="step-icon" aria-hidden="true"><svg viewBox="0 0 32 32"><rect x="7" y="5" width="14" height="18" rx="2" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M11 11h6M11 15h6M11 19h4M18 20l7 4V9l-7 3" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg></span>
        <h3>Post or fill</h3>
        <p>Buyers publish quantity, price, and quality. Farmers and aggregators list supply or accept an order.</p>
      </li>
      <li>
        <span class="step-no">3</span>
        <span class="step-icon" aria-hidden="true"><svg viewBox="0 0 32 32"><path d="M4 20h16.5l3.2-5H28" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><path d="M7 20V13h10l3 5" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><circle cx="10" cy="23.5" r="2.2" fill="none" stroke="currentColor" stroke-width="1.6"/><circle cx="21" cy="23.5" r="2.2" fill="none" stroke="currentColor" stroke-width="1.6"/></svg></span>
        <h3>Move the goods</h3>
        <p>A contract is issued, a haulier quotes, and cover can be attached before the truck leaves.</p>
      </li>
      <li>
        <span class="step-no">4</span>
        <span class="step-icon" aria-hidden="true"><svg viewBox="0 0 32 32"><rect x="4" y="9" width="24" height="16" rx="3" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M4 14h24" stroke="currentColor" stroke-width="1.6"/><circle cx="22.5" cy="19.5" r="1.6" fill="currentColor"/></svg></span>
        <h3>Verify and pay</h3>
        <p>The buyer confirms quality. The wallet settles the supplier, the haulier’s cost, and the desk fee.</p>
      </li>
    </ol>
  </section>

  <section class="section roles">
    <div class="split">
      <h2>Who the desk is for</h2>
      <a href="{{ route('register') }}">Open an account</a>
    </div>
    <div class="role-grid">
      <article class="role role-farmer">
        <span class="role-icon" aria-hidden="true"><svg viewBox="0 0 32 32"><path d="M16 27V15" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M16 18c-6-1-8-6-8-9 6 .2 9 4 8 9zm0-2c6-1 9-6 9-9-6 .2-10 4-9 9z" fill="currentColor"/><path d="M9 27h14" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></span>
        <h3>Farmers</h3>
        <p>List a crop, follow official prices, and apply for an input bundle when the season needs seed and fertiliser.</p>
      </article>
      <article class="role role-aggregator">
        <span class="role-icon" aria-hidden="true"><svg viewBox="0 0 32 32"><path d="M8 18h6v8H8zM14 13h7v13h-7zM21 17h5v9h-5z" fill="currentColor" opacity=".9"/><path d="M6 26h20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></span>
        <h3>Aggregators</h3>
        <p>See processor orders, match them to what you have on the ground, and track the lot until it is paid.</p>
      </article>
      <article class="role role-buyer">
        <span class="role-icon" aria-hidden="true"><svg viewBox="0 0 32 32"><path d="M6 13h20l-2.2 11a5 5 0 0 1-4.8 4H13a5 5 0 0 1-4.8-4z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M11 13V10a5 5 0 0 1 10 0v3" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></span>
        <h3>Buyers</h3>
        <p>Post a buy order with moisture, purity, and a delivery point. Pay from your wallet once quality is confirmed.</p>
      </article>
      <article class="role role-dealer">
        <span class="role-icon" aria-hidden="true"><svg viewBox="0 0 32 32"><path d="M7 13 16 6l9 7v12a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M13 26v-7h6v7" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg></span>
        <h3>Agro-dealers</h3>
        <p>List seed, fertiliser, pesticide, and equipment. Farmers contact you from the public shop.</p>
      </article>
      <article class="role role-haulier">
        <span class="role-icon" aria-hidden="true"><svg viewBox="0 0 32 32"><path d="M4 20h17l3.4-5.2H28" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M7 20V12h10l3.2 5.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><circle cx="10" cy="23" r="2.1" fill="none" stroke="currentColor" stroke-width="1.8"/><circle cx="22" cy="23" r="2.1" fill="none" stroke="currentColor" stroke-width="1.8"/></svg></span>
        <h3>Hauliers</h3>
        <p>Register trucks, publish a rate card, quote open hauls, and update the journey.</p>
      </article>
      <article class="role role-insurer">
        <span class="role-icon" aria-hidden="true"><svg viewBox="0 0 32 32"><path d="M16 5.5 25 9v7.2c0 5.4-3.6 8.6-9 10.8-5.4-2.2-9-5.4-9-10.8V9z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M12.2 16.2 15 19l5-5.4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
        <h3>Insurers</h3>
        <p>Offer cargo cover against a live trade and issue a policy number the buyer can accept.</p>
      </article>
    </div>
  </section>

  <section class="section hubs-band">
    <div class="split">
      <div>
        <p class="kicker">Collection hubs</p>
        <h2>Drop grain where the buyer can receive it.</h2>
      </div>
      <a href="{{ route('hubs') }}">All hubs</a>
    </div>
    <div class="hub-grid">
      @foreach ($centers as $center)
        <article class="hub hub-{{ $loop->index % 4 }}">
          <span class="hub-pin" aria-hidden="true"><svg viewBox="0 0 32 32"><path d="M16 28s8-7.2 8-14a8 8 0 1 0-16 0c0 6.8 8 14 8 14z" fill="none" stroke="currentColor" stroke-width="1.8"/><circle cx="16" cy="14" r="2.6" fill="currentColor"/></svg></span>
          <div>
            <h3>{{ $center->name }}</h3>
            <p class="hub-place">{{ $center->city }}, {{ $center->state }}</p>
            <p>{{ $center->address }}</p>
          </div>
        </article>
      @endforeach
    </div>
  </section>
  <script>
    (function () {
      var root = document.querySelector('[data-order-stage]');
      if (!root) return;
      var cards = Array.prototype.slice.call(root.querySelectorAll('[data-order-card]'));
      var dots = root.querySelectorAll('[data-order-dot]');
      var index = 0;
      var timer = null;

      if (cards.length < 2) {
        root.querySelectorAll('.order-nav, .order-progress').forEach(function (el) { el.hidden = true; });
        if (cards[0]) cards[0].setAttribute('data-pos', 'active');
        return;
      }

      function render() {
        var total = cards.length;
        cards.forEach(function (card, i) {
          var offset = (i - index + total) % total;
          var pos = offset === 0 ? 'active' : offset === 1 ? 'next' : offset === total - 1 ? 'prev' : 'hidden';
          card.setAttribute('data-pos', pos);
        });
        dots.forEach(function (dot, i) {
          dot.classList.toggle('is-active', i === index);
          if (i === index) dot.setAttribute('aria-current', 'true');
          else dot.removeAttribute('aria-current');
        });
        root.style.setProperty('--order-progress', ((index + 1) / total * 100) + '%');
      }

      function go(step) {
        index = (index + step + cards.length) % cards.length;
        render();
      }

      root.querySelector('[data-order-prev]').addEventListener('click', function () { go(-1); });
      root.querySelector('[data-order-next]').addEventListener('click', function () { go(1); });
      cards.forEach(function (card, i) {
        card.addEventListener('click', function () {
          if (card.getAttribute('data-pos') === 'active') return;
          index = i;
          render();
        });
      });
      dots.forEach(function (dot, i) {
        dot.addEventListener('click', function () {
          index = i;
          render();
        });
      });

      function start() {
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
        window.clearInterval(timer);
        timer = window.setInterval(function () { go(1); }, 3800);
      }

      root.addEventListener('mouseenter', function () { window.clearInterval(timer); });
      root.addEventListener('mouseleave', start);
      root.addEventListener('focusin', function () { window.clearInterval(timer); });
      root.addEventListener('focusout', function (event) {
        if (!root.contains(event.relatedTarget)) start();
      });

      render();
      start();
    })();
  </script>
@endsection
