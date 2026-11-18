@extends('layouts.app')
@section('title', 'Alerts')
@section('content')
  <h1>Alerts</h1>
  @forelse ($alerts as $alert)
    <article class="panel">
      <div class="split"><strong>{{ $alert->title }}</strong><span class="meta">{{ $alert->created_at->diffForHumans() }}</span></div>
      <p>{{ $alert->body }}</p>
      @if ($alert->url)
        <form method="POST" action="{{ route('notifications.read', $alert) }}">@csrf<button class="btn small">Open</button></form>
      @elseif (! $alert->read_at)
        <form method="POST" action="{{ route('notifications.read', $alert) }}">@csrf<button class="text-btn">Mark read</button></form>
      @endif
    </article>
  @empty
    <p>No alerts.</p>
  @endforelse
  {{ $alerts->links() }}
@endsection
