@extends('layouts.app')
@section('title', 'Fleet')
@section('content')
  <h1>Fleet and rates</h1>
  <div class="two" style="grid-template-columns: 1fr 1fr">
    <form class="panel stack" method="POST" action="{{ route('logistics.trucks.store') }}">
      @csrf
      <h2>Add a truck</h2>
      <label>Registration<input name="registration_number" required></label>
      <label>Driver<input name="driver_name" required></label>
      <label>Driver phone<input name="driver_phone" required></label>
      <label>Capacity (tonnes)<input type="number" step="0.1" name="capacity_tonnes" required></label>
      <label>Zone
        <select name="route_zone">@foreach(config('marketplace.zones') as $zone)<option>{{ $zone }}</option>@endforeach</select>
      </label>
      <button class="btn">Submit for verification</button>
    </form>
    <form class="panel stack" method="POST" action="{{ route('logistics.rates') }}">
      @csrf
      <h2>Lane rate</h2>
      <label>From<select name="origin_state">@foreach(config('marketplace.states') as $state)<option>{{ $state }}</option>@endforeach</select></label>
      <label>To<select name="destination_state">@foreach(config('marketplace.states') as $state)<option>{{ $state }}</option>@endforeach</select></label>
      <label>₦ per tonne<input type="number" step="0.01" name="rate_per_tonne" required></label>
      <button class="btn">Save rate</button>
    </form>
  </div>
  <div class="table-wrap" style="margin-top:1rem">
    <table>
      <thead><tr><th>Truck</th><th>Driver</th><th>Capacity</th><th>Zone</th><th>Status</th><th></th></tr></thead>
      <tbody>
        @foreach ($trucks as $truck)
          <tr>
            <td>{{ $truck->registration_number }}</td>
            <td>{{ $truck->driver_name }} · {{ $truck->driver_phone }}</td>
            <td>{{ $truck->capacity_tonnes }} t</td>
            <td>{{ $truck->route_zone }}</td>
            <td>@include('partials.status', ['status' => $truck->status, 'group' => 'user_statuses']) {{ $truck->availability }}</td>
            <td>
              <form method="POST" action="{{ route('logistics.trucks.availability', $truck) }}">@csrf
                <input type="hidden" name="availability" value="{{ $truck->availability === 'available' ? 'unavailable' : 'available' }}">
                <button class="text-btn">Toggle availability</button>
              </form>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  <h2>Rate card</h2>
  @forelse ($rates as $rate)
    <p>{{ $rate->origin_state }} → {{ $rate->destination_state }} · {{ naira($rate->rate_per_tonne) }}/t</p>
  @empty
    <p class="meta">No lanes yet.</p>
  @endforelse
@endsection
