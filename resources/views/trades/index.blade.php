@extends('layouts.app')
@section('title', 'Trades')
@section('content')
  <h1>Trade file</h1>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Code</th><th>Commodity</th><th>Tonnes</th><th>Buyer</th><th>Supplier</th><th>Status</th><th>Buyer total</th></tr></thead>
      <tbody>
        @forelse ($trades as $trade)
          <tr>
            <td><a href="{{ route('trades.show', $trade) }}">{{ $trade->code }}</a></td>
            <td>{{ $trade->commodity->name }}</td>
            <td>{{ $trade->quantity }}</td>
            <td>{{ $trade->buyer->deskName() }}</td>
            <td>{{ $trade->supplier->deskName() }}</td>
            <td>@include('partials.status', ['status' => $trade->status, 'group' => 'trade_statuses'])</td>
            <td>{{ naira($trade->buyer_total) }}</td>
          </tr>
        @empty
          <tr><td colspan="7">Nothing in the file yet.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  {{ $trades->links() }}
@endsection
