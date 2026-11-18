@extends('layouts.app')
@section('title', 'Bundles')
@section('content')
  <h1>Input bundles</h1>
  <form class="form-grid panel" method="POST" action="{{ route('admin.bundles.store') }}">
    @csrf
    <label>Name<input name="name" required></label>
    <label>Season<input name="season" placeholder="2026 wet season" required></label>
    <label>Hectares<input type="number" step="0.1" name="coverage_ha" required></label>
    <label>Price<input type="number" step="0.01" name="price" required></label>
    <label>Farmer contribution %<input type="number" step="0.1" name="contribution_percent" required></label>
    <label class="wide">Description<textarea name="description"></textarea></label>
    <label class="wide">Items, one per line<textarea name="items"></textarea></label>
    <div class="wide"><button class="btn">Publish bundle</button></div>
  </form>
  @foreach ($bundles as $bundle)
    <p>{{ $bundle->name }} · {{ $bundle->season }} · {{ naira($bundle->price) }} · {{ $bundle->active ? 'live' : 'hidden' }}</p>
  @endforeach
@endsection
