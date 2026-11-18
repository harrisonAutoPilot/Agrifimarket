@extends('layouts.app')
@section('title', 'Input shop')
@section('content')
  <h1>Your input listings</h1>
  <form class="form-grid panel" method="POST" action="{{ route('dealer.store') }}">
    @csrf
    <label>Name<input name="name" required></label>
    <label>Category
      <select name="category">
        @foreach (['seed' => 'Seed', 'fertilizer' => 'Fertiliser', 'pesticide' => 'Pesticide', 'equipment' => 'Equipment'] as $value => $label)
          <option value="{{ $value }}">{{ $label }}</option>
        @endforeach
      </select>
    </label>
    <label>Brand<input name="brand"></label>
    <label>Price<input type="number" step="0.01" name="price" required></label>
    <label>Unit<input name="unit" placeholder="50kg bag" required></label>
    <label>Stock<input type="number" step="1" name="stock" required></label>
    <label>State<select name="state">@foreach(config('marketplace.states') as $state)<option>{{ $state }}</option>@endforeach</select></label>
    <label>City<input name="city" required></label>
    <label class="wide">Specifications<input name="specifications"></label>
    <label class="wide">Description<textarea name="description"></textarea></label>
    <div class="wide"><button class="btn">Submit for approval</button></div>
  </form>
  <div class="table-wrap" style="margin-top:1rem">
    <table>
      <thead><tr><th>Item</th><th>Price</th><th>Where</th><th>Notes</th><th>Status</th></tr></thead>
      <tbody>
        @foreach ($listings as $listing)
          <tr>
            <td>{{ $listing->name }}</td>
            <td>{{ naira($listing->price) }}/{{ $listing->unit }}</td>
            <td>{{ $listing->city }}, {{ $listing->state }}</td>
            <td>{{ $listing->inquiries_count }} enquiries</td>
            <td>@include('partials.status', ['status' => $listing->status])</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  <h2>Enquiries</h2>
  @forelse ($inquiries as $inquiry)
    <article class="panel"><strong>{{ $inquiry->listing->name }}</strong><p>{{ $inquiry->message }}</p><p class="meta">{{ $inquiry->name }} · {{ $inquiry->phone }} · {{ $inquiry->email }}</p></article>
  @empty
    <p class="meta">No enquiries yet.</p>
  @endforelse
@endsection
