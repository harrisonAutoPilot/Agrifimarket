@extends('layouts.app')
@section('title', 'Matches')
@section('content')
  <h1>Suggested pairs</h1>
  <p class="meta">Same commodity, with a score for price, state, moisture, and purity.</p>
  <div class="cards">
    @forelse ($matches as $match)
      <article class="order-card">
        <div class="split"><strong>Score {{ $match->score }}</strong><span class="meta">{{ $match->supply->code }} → {{ $match->demand->code }}</span></div>
        <p>{{ $match->supply->commodity->name }} · {{ tonnes($match->supply->quantity) }} offered at {{ naira($match->supply->price_per_kg) }}/kg</p>
        <p class="meta">Buyer bid {{ naira($match->demand->price_per_kg) }}/kg in {{ $match->demand->state }} · seller in {{ $match->supply->state }}</p>
        <p class="meta">{{ implode(' · ', $match->reasons ?? []) }}</p>
        <form method="POST" action="{{ route('matches.agree', $match) }}" style="display:inline">@csrf<button class="btn small">Agree trade</button></form>
        <form method="POST" action="{{ route('matches.dismiss', $match) }}" style="display:inline">@csrf<button class="text-btn">Dismiss</button></form>
      </article>
    @empty
      <p>No open matches. List supply or post an order and the desk will look again.</p>
    @endforelse
  </div>
  {{ $matches->links() }}
@endsection
