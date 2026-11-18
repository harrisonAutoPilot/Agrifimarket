@extends('layouts.public')
@section('title', 'Support')
@section('content')
  <section class="section">
    <div class="grid-3">
      <div class="wide" style="grid-column: span 2">
        <p class="kicker">Help</p>
        <h1>How the desk works.</h1>
        <article class="panel"><h3>Who can join?</h3><p>Farmers, aggregators, buyers and processors, agro-dealers, hauliers, and insurers. Each desk stays closed until documents are approved.</p></article>
        <article class="panel"><h3>How do I sell grain?</h3><p>List what you have, or accept a live buy order and name the quantity you can fill. A trade file opens from that acceptance.</p></article>
        <article class="panel"><h3>How do I get paid?</h3><p>After the buyer confirms quality, settlement moves from the buyer wallet to the supplier wallet, less the desk commission. An operator can also fund a wallet while bank rails are being connected.</p></article>
        <article class="panel"><h3>What if quality is off?</h3><p>Open a dispute on the trade. An operator reads the note and either returns the trade to its last stage or closes it.</p></article>
        <article class="panel"><h3>Is logistics included?</h3><p>Platform delivery invites hauliers to quote. Self-delivery lets the supplier move the lot and record the cost up front.</p></article>
      </div>
      <form class="panel stack" method="POST" action="{{ route('support.store') }}">
        @csrf
        <h2>Write to the desk</h2>
        <label>Name<input name="name" value="{{ old('name') }}" required></label>
        <label>Email<input type="email" name="email" value="{{ old('email') }}" required></label>
        <label>Phone<input name="phone" value="{{ old('phone') }}"></label>
        <label>Message<textarea name="message" required>{{ old('message') }}</textarea></label>
        <button class="btn">Send</button>
        <p class="meta">{{ config('marketplace.contact_email') }} · {{ config('marketplace.contact_phone') }}</p>
      </form>
    </div>
  </section>
@endsection
