@extends('layouts.app')
@section('title', 'My supply')
@section('content')
  <div class="page-head">
    <h1>Supply on your book</h1>
    <a class="btn" href="{{ route('supplies.create') }}">New listing</a>
  </div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Code</th><th>Commodity</th><th>Tonnes</th><th>₦/kg</th><th>Where</th><th>Status</th><th></th></tr></thead>
      <tbody>
        @forelse ($listings as $listing)
          <tr>
            <td>{{ $listing->code }}</td>
            <td>{{ $listing->commodity->name }}</td>
            <td>{{ $listing->quantity }}</td>
            <td>{{ naira($listing->price_per_kg) }}</td>
            <td>{{ $listing->city }}, {{ $listing->state }}</td>
            <td>@include('partials.status', ['status' => $listing->status])</td>
            <td>
              @if (in_array($listing->status, ['draft', 'active', 'paused']))
                <a href="{{ route('supplies.edit', $listing) }}">Edit</a>
                <form method="POST" action="{{ route('supplies.status', $listing) }}" style="display:inline">@csrf
                  <input type="hidden" name="status" value="{{ $listing->status === 'active' ? 'paused' : 'active' }}">
                  <button class="text-btn">{{ $listing->status === 'active' ? 'Pause' : 'Activate' }}</button>
                </form>
                <form method="POST" action="{{ route('supplies.status', $listing) }}" style="display:inline">@csrf
                  <input type="hidden" name="status" value="withdrawn">
                  <button class="text-btn">Withdraw</button>
                </form>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="7">No supply yet. List a lot so buyers and the matcher can see it.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  {{ $listings->links() }}
@endsection
