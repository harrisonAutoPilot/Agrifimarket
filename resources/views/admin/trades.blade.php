@extends('layouts.app')
@section('title', 'All trades')
@section('content')
  <div class="page-head"><h1>All trades</h1><a href="{{ route('admin.reports.export') }}">Export CSV</a></div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Code</th><th>Commodity</th><th>Parties</th><th>Status</th><th>Value</th></tr></thead>
      <tbody>
        @foreach ($trades as $trade)
          <tr>
            <td><a href="{{ route('trades.show', $trade) }}">{{ $trade->code }}</a></td>
            <td>{{ $trade->commodity->name }} · {{ tonnes($trade->quantity) }}</td>
            <td>{{ $trade->buyer->deskName() }} / {{ $trade->supplier->deskName() }}</td>
            <td>@include('partials.status', ['status' => $trade->status, 'group' => 'trade_statuses'])</td>
            <td>{{ naira($trade->commodity_value) }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  {{ $trades->links() }}
@endsection
