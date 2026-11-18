<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>{{ $trade->code }} statement</title>
  <link rel="stylesheet" href="{{ asset('css/agrifi.css') }}">
</head>
<body>
  <main class="section narrow">
    <p class="kicker">AgrifiMarket statement</p>
    <h1>{{ $trade->code }}</h1>
    <p>{{ $trade->commodity->name }} · {{ tonnes($trade->quantity) }} at {{ naira($trade->price_per_kg) }}/kg</p>
    <p>Buyer {{ $trade->buyer->deskName() }} · Supplier {{ $trade->supplier->deskName() }}</p>
    <p>Status {{ status_label('trade_statuses', $trade->status) }}</p>
    <div class="table-wrap">
      <table>
        <tbody>
          <tr><td>Commodity value</td><td>{{ naira($trade->commodity_value) }}</td></tr>
          <tr><td>Haulage</td><td>{{ naira($trade->logistics_cost) }}</td></tr>
          <tr><td>Insurance</td><td>{{ naira($trade->insurance_premium) }}</td></tr>
          <tr><td>Desk fee</td><td>{{ naira($trade->platform_fee) }}</td></tr>
          <tr><td>Commission kept from supplier</td><td>{{ naira($trade->commission) }}</td></tr>
          <tr><td>Buyer total</td><td>{{ naira($trade->buyer_total) }}</td></tr>
          <tr><td>Supplier net</td><td>{{ naira($trade->supplier_net) }}</td></tr>
        </tbody>
      </table>
    </div>
    <h2>Trail</h2>
    <ol>
      @foreach ($trade->events as $event)
        <li>{{ $event->created_at->format('d M Y H:i') }} — {{ status_label('trade_statuses', $event->status) }}. {{ $event->note }}</li>
      @endforeach
    </ol>
    <button class="btn no-print" onclick="window.print()">Print or save PDF</button>
  </main>
</body>
</html>
