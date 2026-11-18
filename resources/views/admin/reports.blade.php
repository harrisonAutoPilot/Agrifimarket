@extends('layouts.app')
@section('title', 'Reports')
@section('content')
  <div class="page-head"><h1>Desk report</h1><a class="btn secondary" href="{{ route('admin.reports.export') }}">Export trades</a></div>
  <p>Settled commodity value {{ naira($settled) }}</p>
  <h2>Trades</h2>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Status</th><th>Count</th><th>Commodity value</th></tr></thead>
      <tbody>
        @foreach ($byStatus as $row)
          <tr><td>{{ status_label('trade_statuses', $row->status) }}</td><td>{{ $row->total }}</td><td>{{ naira($row->value) }}</td></tr>
        @endforeach
      </tbody>
    </table>
  </div>
  <h2>Members</h2>
  @foreach ($members as $row)
    <p>{{ role_label($row->role) }} · {{ $row->total }}</p>
  @endforeach
@endsection
