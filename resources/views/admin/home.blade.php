@extends('layouts.app')
@section('title', 'Queue')
@section('content')
  @php
    $waiting = $pendingUsers->count() + $pendingDocs->count() + $creditQueue->count() + $pendingListings->count() + $pendingTrucks->count() + $openDisputes->count();
  @endphp
  <header class="queue-head">
    <p class="kicker">Operator</p>
    <h1>Queue</h1>
    <p>{{ $waiting === 0 ? 'Nothing is waiting on the desk.' : $waiting.' '.str('item')->plural($waiting).' waiting for a decision.' }}</p>
  </header>
  <div class="queue-board">
    <section class="queue-lane queue-desks">
      <header>
        <span class="queue-mark" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="8" r="3.2"/><path d="M5.5 19.2c1.2-2.8 3.4-4.2 6.5-4.2s5.3 1.4 6.5 4.2"/></svg></span>
        <h2>Desks</h2>
        <b class="queue-count">{{ $pendingUsers->count() }}</b>
      </header>
      @forelse ($pendingUsers as $member)
        <article class="queue-item">
          <a href="{{ route('admin.users.show', $member) }}"><strong>{{ $member->deskName() }}</strong></a>
          <p class="meta">{{ role_label($member->role) }} · @include('partials.status', ['status' => $member->status, 'group' => 'user_statuses'])</p>
        </article>
      @empty
        <p class="queue-empty">No desks waiting.</p>
      @endforelse
    </section>

    <section class="queue-lane queue-docs">
      <header>
        <span class="queue-mark" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M7 3.5h7.2L19 8.2V20.5H7z"/><path d="M14 3.8V8.4h4.4M9.2 12.2h6.2M9.2 15.6h4.4"/></svg></span>
        <h2>Documents</h2>
        <b class="queue-count">{{ $pendingDocs->count() }}</b>
      </header>
      @forelse ($pendingDocs as $document)
        <article class="queue-item">
          <strong>{{ $document->user->deskName() }}</strong>
          <p class="meta">{{ config('marketplace.document_types.'.$document->type) }}</p>
          <form method="POST" action="{{ route('admin.documents', $document) }}">@csrf
            <button class="btn small" name="status" value="approved">Approve</button>
            <button class="text-btn queue-reject" name="status" value="rejected">Reject</button>
          </form>
        </article>
      @empty
        <p class="queue-empty">No documents waiting.</p>
      @endforelse
    </section>

    <section class="queue-lane queue-credit">
      <header>
        <span class="queue-mark" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><ellipse cx="12" cy="7" rx="6.2" ry="2.3"/><path d="M5.8 7v3.4c0 1.3 2.8 2.3 6.2 2.3s6.2-1 6.2-2.3V7"/><path d="M5.8 10.4v3.4c0 1.3 2.8 2.3 6.2 2.3s6.2-1 6.2-2.3v-3.4"/></svg></span>
        <h2>Input credit</h2>
        <b class="queue-count">{{ $creditQueue->count() }}</b>
      </header>
      @forelse ($creditQueue as $application)
        <article class="queue-item">
          <strong>{{ $application->farmer->deskName() }}</strong>
          <p class="meta">{{ $application->bundle->name }}</p>
          <form method="POST" action="{{ route('admin.credit', $application) }}">@csrf<button class="btn small">Approve contribution</button></form>
        </article>
      @empty
        <p class="queue-empty">No applications waiting.</p>
      @endforelse
    </section>

    <section class="queue-lane queue-shop">
      <header>
        <span class="queue-mark" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 10.5 6.2 5h11.6L20 10.5"/><path d="M5 10.5h14V19H5z"/><path d="M10 19v-4.2h4V19"/></svg></span>
        <h2>Dealer listings</h2>
        <b class="queue-count">{{ $pendingListings->count() }}</b>
      </header>
      @forelse ($pendingListings as $listing)
        <article class="queue-item">
          <strong>{{ $listing->name }}</strong>
          @php($stock = (float) $listing->stock)
          <p class="meta">{{ $listing->dealer->deskName() }} · {{ rtrim(rtrim(number_format($stock, 2), '0'), '.') }} {{ $stock == 1 ? $listing->unit : str($listing->unit)->plural() }} · {{ naira($listing->price, 0) }}</p>
          <form method="POST" action="{{ route('admin.listings', $listing) }}">@csrf
            <button class="btn small" name="status" value="active">Publish</button>
            <button class="text-btn queue-reject" name="status" value="rejected">Reject</button>
          </form>
        </article>
      @empty
        <p class="queue-empty">Shop queue is clear.</p>
      @endforelse
    </section>

    <section class="queue-lane queue-trucks">
      <header>
        <span class="queue-mark" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M3 7.5h11v8H3zM14 11h4.2L21 14.2V15.5h-7"/><circle cx="7" cy="17.2" r="1.5"/><circle cx="17.2" cy="17.2" r="1.5"/></svg></span>
        <h2>Trucks</h2>
        <b class="queue-count">{{ $pendingTrucks->count() }}</b>
      </header>
      @forelse ($pendingTrucks as $truck)
        <article class="queue-item">
          <strong>{{ $truck->registration_number }}</strong>
          <p class="meta">{{ $truck->owner->deskName() }} · {{ rtrim(rtrim(number_format((float) $truck->capacity_tonnes, 2), '0'), '.') }} t · {{ $truck->route_zone }}</p>
          <form method="POST" action="{{ route('admin.trucks', $truck) }}">@csrf
            <button class="btn small" name="status" value="verified">Verify</button>
            <button class="text-btn queue-reject" name="status" value="rejected">Reject</button>
          </form>
        </article>
      @empty
        <p class="queue-empty">No trucks waiting.</p>
      @endforelse
    </section>

    <section class="queue-lane queue-disputes">
      <header>
        <span class="queue-mark" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 4.2 4.2 18.2h15.6z"/><path d="M12 9.4v4.2M12 16.4h.01"/></svg></span>
        <h2>Disputes</h2>
        <b class="queue-count">{{ $openDisputes->count() }}</b>
      </header>
      @forelse ($openDisputes as $dispute)
        <article class="queue-item">
          <a href="{{ route('trades.show', $dispute->trade) }}"><strong>{{ $dispute->trade->code }}</strong></a>
          <p class="meta">{{ $dispute->raiser->deskName() }}</p>
          <p>{{ $dispute->reason }}</p>
        </article>
      @empty
        <p class="queue-empty">No open disputes.</p>
      @endforelse
    </section>
  </div>
@endsection
