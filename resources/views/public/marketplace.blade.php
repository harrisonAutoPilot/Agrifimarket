@extends('layouts.public')
@section('title', 'Farm inputs')
@section('content')
  <section class="section">
    <div class="page-head">
      <div>
        <p class="kicker">Agro-dealer shop</p>
        <h1>Seed, fertiliser, and tools from approved dealers.</h1>
      </div>
      @guest
        <a class="btn" href="{{ route('register') }}">Sell inputs</a>
      @endguest
    </div>
    <form class="filters" method="GET">
      <label class="wide">Search
        <input name="q" value="{{ request('q') }}" placeholder="Name, brand, or city">
      </label>
      <label>Category
        <select name="category">
          <option value="">All</option>
          @foreach (['seed' => 'Seed', 'fertilizer' => 'Fertiliser', 'pesticide' => 'Pesticide', 'equipment' => 'Equipment'] as $key => $label)
            <option value="{{ $key }}" @selected(request('category') === $key)>{{ $label }}</option>
          @endforeach
        </select>
      </label>
      <label>State
        <select name="state">
          <option value="">All</option>
          @foreach (config('marketplace.states') as $state)
            <option @selected(request('state') === $state)>{{ $state }}</option>
          @endforeach
        </select>
      </label>
      <button class="btn small">Search</button>
    </form>
    <div class="cards">
      @forelse ($listings as $listing)
        <a class="order-card" href="{{ route('marketplace.show', $listing) }}" style="text-decoration:none">
          <p class="kicker">{{ ucfirst($listing->category) }}</p>
          <h3>{{ $listing->name }}</h3>
          <p class="money">{{ naira($listing->price) }} / {{ $listing->unit }}</p>
          <p class="meta">{{ $listing->brand }} · {{ $listing->city }}, {{ $listing->state }}</p>
          <p class="meta">{{ $listing->dealer->deskName() }}</p>
        </a>
      @empty
        <p>No inputs are live for that search. Dealers publish here after approval.</p>
      @endforelse
    </div>
    {{ $listings->links() }}
  </section>
@endsection
