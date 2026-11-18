@extends('layouts.app')
@section('title', 'Hubs')
@section('content')
  <header class="queue-head">
    <p class="kicker">Receiving points</p>
    <h1>Collection hubs</h1>
    <p>Lots are dropped at these yards so a buyer can take them up.</p>
  </header>
  <form class="form-grid panel" method="POST" action="{{ route('admin.centers.store') }}">
    @csrf
    <label>Name<input name="name" required></label>
    <label>State<select name="state">@foreach(config('marketplace.states') as $state)<option>{{ $state }}</option>@endforeach</select></label>
    <label>City<input name="city" required></label>
    <label>Phone<input name="phone"></label>
    <label class="wide">Address<input name="address" required></label>
    <div class="wide"><button class="btn">Add hub</button></div>
  </form>

  <section class="book">
    <div class="book-head">
      <div>
        <h2>Hubs on the desk</h2>
        <p>{{ $centers->count() }} {{ str('place')->plural($centers->count()) }} where grain can be received.</p>
      </div>
    </div>
    @if ($centers->isEmpty())
      <p class="queue-empty">No hubs yet.</p>
    @else
      <div class="hub-grid">
        @foreach ($centers as $center)
          <article class="hub hub-{{ $loop->index % 4 }}">
            <span class="hub-pin" aria-hidden="true"><svg viewBox="0 0 32 32"><path d="M16 28s8-7.2 8-14a8 8 0 1 0-16 0c0 6.8 8 14 8 14z" fill="none" stroke="currentColor" stroke-width="1.8"/><circle cx="16" cy="14" r="2.6" fill="currentColor"/></svg></span>
            <div>
              <h3>{{ $center->name }}</h3>
              <p class="hub-place">{{ $center->city }}, {{ $center->state }}</p>
              <p>{{ $center->address }}@if($center->phone) · {{ $center->phone }}@endif</p>
            </div>
          </article>
        @endforeach
      </div>
    @endif
  </section>
@endsection
