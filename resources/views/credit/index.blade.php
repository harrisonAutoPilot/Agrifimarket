@extends('layouts.app')
@section('title', 'Input credit')
@section('content')
  <h1>Season bundles</h1>
  <div class="cards">
    @foreach ($bundles as $bundle)
      <article class="order-card">
        <p class="kicker">{{ $bundle->season }} · {{ $bundle->coverage_ha }} ha</p>
        <h3>{{ $bundle->name }}</h3>
        <p>{{ $bundle->description }}</p>
        <p class="money">{{ naira($bundle->price) }} · you contribute {{ $bundle->contribution_percent }}% ({{ naira($bundle->contributionAmount()) }})</p>
        <p class="meta">{{ implode(', ', $bundle->items ?? []) }}</p>
        <form method="POST" action="{{ route('credit.apply', $bundle) }}">@csrf<button class="btn small">Apply</button></form>
      </article>
    @endforeach
  </div>
  <h2>Your applications</h2>
  @forelse ($applications as $application)
    <p><a href="{{ route('credit.show', $application) }}">{{ $application->bundle->name }}</a> · @include('partials.status', ['status' => $application->status, 'group' => 'trade_statuses']) · outstanding {{ naira($application->outstanding) }}</p>
  @empty
    <p class="meta">No applications yet.</p>
  @endforelse
@endsection
