@extends('layouts.public')
@section('title', 'Collection hubs')
@section('content')
  <section class="section">
    <p class="kicker">Receiving points</p>
    <h1>Where to drop off the grain.</h1>
    <div class="cards">
      @foreach ($centers as $center)
        <article class="panel">
          <h2>{{ $center->name }}</h2>
          <p>{{ $center->address }}</p>
          <p class="meta">{{ $center->city }}, {{ $center->state }}</p>
          @if ($center->phone)<p class="meta">{{ $center->phone }}</p>@endif
        </article>
      @endforeach
    </div>
  </section>
@endsection
