@extends('layouts.app')
@section('title', 'Application')
@section('content')
  <p class="kicker">{{ $application->bundle->season }}</p>
  <h1>{{ $application->bundle->name }}</h1>
  <p>@include('partials.status', ['status' => $application->status, 'group' => 'trade_statuses'])</p>
  <div class="stats">
    <article class="stat"><b>{{ naira($application->contribution_amount) }}</b><span class="meta">Your contribution</span></article>
    <article class="stat"><b>{{ naira($application->financed_amount) }}</b><span class="meta">Financed</span></article>
    <article class="stat"><b>{{ naira($application->outstanding) }}</b><span class="meta">Still to repay</span></article>
  </div>
  @if ($application->status === 'contribution_due' && auth()->id() === $application->user_id)
    <form method="POST" action="{{ route('credit.contribute', $application) }}">@csrf<button class="btn">Pay contribution from wallet</button></form>
  @endif
  @if ($application->status === 'disbursed' && auth()->id() === $application->user_id)
    <form method="POST" action="{{ route('credit.pickup', $application) }}">@csrf<button class="btn">I have collected the inputs</button></form>
  @endif
  @if ($application->status === 'repaying' && auth()->id() === $application->user_id)
    <form method="POST" action="{{ route('credit.repay', $application) }}" class="stack" style="max-width:320px">
      @csrf
      <label>Repay amount<input type="number" step="0.01" name="amount" value="{{ $application->outstanding }}" required></label>
      <button class="btn">Repay from wallet</button>
    </form>
  @endif
  @if ($application->status === 'submitted')
    <p class="meta">An operator still needs to approve this application.</p>
  @endif
@endsection
