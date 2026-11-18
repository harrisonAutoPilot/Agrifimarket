@extends('layouts.app')
@section('title', 'My orders')
@section('content')
  <div class="page-head">
    <h1>Buy orders</h1>
    <a class="btn" href="{{ route('demands.create') }}">Post an order</a>
  </div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Code</th><th>Commodity</th><th>Open t</th><th>₦/kg</th><th>Delivery</th><th>Status</th><th></th></tr></thead>
      <tbody>
        @forelse ($orders as $order)
          <tr>
            <td>{{ $order->code }}</td>
            <td>{{ $order->commodity->name }}</td>
            <td>{{ $order->remaining() }} / {{ $order->quantity }}</td>
            <td>{{ naira($order->price_per_kg) }}</td>
            <td>{{ $order->city }}, {{ $order->state }}</td>
            <td>@include('partials.status', ['status' => $order->status])</td>
            <td>
              @if (in_array($order->status, ['draft', 'active', 'paused']))
                <a href="{{ route('demands.edit', $order) }}">Edit</a>
                <form method="POST" action="{{ route('demands.status', $order) }}" style="display:inline">@csrf
                  <input type="hidden" name="status" value="withdrawn"><button class="text-btn">Withdraw</button>
                </form>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="7">You have not posted an order yet.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  {{ $orders->links() }}
@endsection
