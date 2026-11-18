@extends('layouts.app')
@section('title', 'Disputes')
@section('content')
  <h1>Disputes</h1>
  @foreach ($disputes as $dispute)
    <article class="panel">
      <h2><a href="{{ route('trades.show', $dispute->trade) }}">{{ $dispute->trade->code }}</a> · {{ $dispute->trade->commodity->name }}</h2>
      <p>{{ $dispute->reason }}</p>
      <p class="meta">{{ $dispute->raiser->deskName() }} · @include('partials.status', ['status' => $dispute->status, 'group' => 'trade_statuses'])</p>
      @if ($dispute->status === 'open')
        <form class="stack" method="POST" action="{{ route('admin.disputes.resolve', $dispute) }}">
          @csrf
          <label>Outcome
            <select name="outcome">
              <option value="restore">Return the trade to its previous stage</option>
              <option value="close">Close the trade</option>
            </select>
          </label>
          <label>Resolution<textarea name="resolution" required></textarea></label>
          <button class="btn">Resolve</button>
        </form>
      @else
        <p>{{ $dispute->resolution }}</p>
      @endif
    </article>
  @endforeach
  {{ $disputes->links() }}
@endsection
