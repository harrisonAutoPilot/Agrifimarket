@extends('layouts.app')
@section('title', 'Wallet')
@section('content')
  <p class="kicker">{{ $wallet->currency }} wallet</p>
  <h1>{{ naira($wallet->balance) }}</h1>
  <p class="meta">Settlement stays on the desk until a bank payout rail is connected. An operator can credit this wallet.</p>
  <div class="table-wrap">
    <table>
      <thead><tr><th>When</th><th>Narration</th><th>Type</th><th>Amount</th><th>Balance</th><th>Reference</th></tr></thead>
      <tbody>
        @forelse ($transactions as $row)
          <tr>
            <td>{{ $row->created_at->format('d M Y H:i') }}</td>
            <td>{{ $row->narration }}</td>
            <td>{{ ucfirst($row->type) }}</td>
            <td>{{ naira($row->amount) }}</td>
            <td>{{ naira($row->balance_after) }}</td>
            <td>{{ $row->reference }}</td>
          </tr>
        @empty
          <tr><td colspan="6">No movements yet.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  {{ $transactions->links() }}
@endsection
