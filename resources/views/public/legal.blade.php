@extends('layouts.public')
@section('title', $page === 'terms' ? 'Terms' : 'Privacy')
@section('content')
  <section class="section narrow">
    @if ($page === 'terms')
      <p class="kicker">Terms</p>
      <h1>Using AgrifiMarket.</h1>
      <p>AgrifiMarket is a commodity desk operated for Voriancorelli Limited. These terms cover the website and the signed-in desks.</p>
      <h2>Accounts</h2>
      <p>You must be 18 or older, or duly authorised for a business. You provide a working email, a Nigerian phone number, and documents an operator can review. You keep your password to yourself.</p>
      <h2>Orders</h2>
      <p>A buy order is an offer. When a farmer or aggregator accepts a quantity, that quantity becomes a trade. Withdrawing an order that is already matched does not cancel the trade.</p>
      <h2>Quality</h2>
      <p>Tell the desk within the trade file if delivered goods miss the moisture, purity, or grade that was agreed. An operator facilitates the dispute and is not the seller of the commodity.</p>
      <h2>Money</h2>
      <p>Settlement runs through AgrifiMarket wallets. The desk charges the commission and flat fee shown on the trade before payment. Bank payout beyond the wallet will be added when a payment partner is connected.</p>
      <h2>Law</h2>
      <p>These terms follow the laws of the Federal Republic of Nigeria. Write to {{ config('marketplace.contact_email') }} before treating a disagreement as a formal dispute outside the desk.</p>
    @else
      <p class="kicker">Privacy</p>
      <h1>What the desk keeps.</h1>
      <p>We collect the name, email, phone, role, business details, and documents you upload so we can open and run your desk. Trades store quantity, price, quality, delivery, and wallet movements.</p>
      <p>Identity numbers are used for compliance review and are not shown on public pages. We do not sell the book of trades. Operators and the counterparties on a trade can see the fields that trade requires.</p>
      <p>You can ask {{ config('marketplace.contact_email') }} what we hold and for a correction. We keep trade records for as long as the desk needs them for settlement and audit.</p>
    @endif
  </section>
@endsection
