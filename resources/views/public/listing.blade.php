@extends('layouts.public')
@section('title', $listing->name)
@section('content')
  <section class="section narrow">
    <p class="kicker">{{ ucfirst($listing->category) }} · {{ $listing->dealer->deskName() }}</p>
    <h1>{{ $listing->name }}</h1>
    <p class="money" style="font-size:1.4rem">{{ naira($listing->price) }} per {{ $listing->unit }}</p>
    <p>{{ $listing->description }}</p>
    <p class="meta">{{ $listing->brand }} · {{ $listing->specifications }}</p>
    <p class="meta">{{ $listing->city }}, {{ $listing->state }} · stock {{ number_format($listing->stock, 0) }}</p>
    <hr class="line">
    <h2>Contact the dealer</h2>
    <form class="stack" method="POST" action="{{ route('marketplace.inquire', $listing) }}">
      @csrf
      <label>Name<input name="name" value="{{ old('name', auth()->user()->name ?? '') }}" required></label>
      <label>Phone<input name="phone" value="{{ old('phone', auth()->user()->phone ?? '') }}" required></label>
      <label>Email<input type="email" name="email" value="{{ old('email', auth()->user()->email ?? '') }}" required></label>
      <label>Message<textarea name="message" required>{{ old('message') }}</textarea></label>
      <button class="btn" type="submit">Send note</button>
    </form>
  </section>
@endsection
