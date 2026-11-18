@extends('layouts.app')
@section('title', $listing->exists ? 'Edit supply' : 'New supply')
@section('content')
  <h1>{{ $listing->exists ? 'Edit '.$listing->code : 'List supply' }}</h1>
  <form class="form-grid panel" method="POST" action="{{ $listing->exists ? route('supplies.update', $listing) : route('supplies.store') }}" enctype="multipart/form-data">
    @csrf
    @if ($listing->exists) @method('PUT') @endif
    <label>Commodity
      <select name="commodity_id" required>
        @foreach ($commodities as $commodity)
          <option value="{{ $commodity->id }}" @selected(old('commodity_id', $listing->commodity_id) == $commodity->id)>{{ $commodity->name }}</option>
        @endforeach
      </select>
    </label>
    <label>Tonnes<input type="number" step="0.01" name="quantity" value="{{ old('quantity', $listing->quantity) }}" required></label>
    <label>Price per kg<input type="number" step="0.01" name="price_per_kg" value="{{ old('price_per_kg', $listing->price_per_kg) }}" required></label>
    <label>Grade
      <select name="grade">
        @foreach (config('marketplace.grades') as $grade)
          <option @selected(old('grade', $listing->grade) === $grade)>{{ $grade }}</option>
        @endforeach
      </select>
    </label>
    <label>Moisture %<input type="number" step="0.1" name="moisture" value="{{ old('moisture', $listing->moisture) }}"></label>
    <label>Purity %<input type="number" step="0.1" name="purity" value="{{ old('purity', $listing->purity) }}"></label>
    <label>State
      <select name="state" required>
        @foreach (config('marketplace.states') as $state)
          <option @selected(old('state', $listing->state) === $state)>{{ $state }}</option>
        @endforeach
      </select>
    </label>
    <label>City<input name="city" value="{{ old('city', $listing->city) }}" required></label>
    <label class="wide">Pickup address<input name="address" value="{{ old('address', $listing->address) }}"></label>
    <label>Expires<input type="date" name="expires_on" value="{{ old('expires_on', optional($listing->expires_on)->toDateString()) }}"></label>
    <label>Visibility
      <select name="status">
        <option value="active" @selected(old('status', $listing->status) === 'active')>Active</option>
        <option value="draft" @selected(old('status', $listing->status) === 'draft')>Draft</option>
      </select>
    </label>
    <label class="wide">Notes<textarea name="description">{{ old('description', $listing->description) }}</textarea></label>
    <label class="wide">Assay, photo, or receipt<input type="file" name="files[]" multiple accept=".jpg,.jpeg,.png,.pdf"></label>
    <div class="wide"><button class="btn">Save listing</button></div>
  </form>
@endsection
