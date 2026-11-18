@extends('layouts.app')
@section('title', $member->deskName())
@section('content')
  <h1>{{ $member->deskName() }}</h1>
  <p>{{ $member->name }} · {{ role_label($member->role) }} · {{ $member->email }} · {{ $member->phone }}</p>
  <p>{{ $member->address }} {{ $member->city }}, {{ $member->state }}</p>
  <p class="meta">Bank {{ $member->bank_name }} · {{ $member->bank_account_name }} · {{ $member->bank_account_number ? '••••'.substr($member->bank_account_number, -4) : 'no account yet' }}</p>
  <p>Wallet {{ naira($member->wallet->balance ?? 0) }}</p>
  <form class="panel stack" method="POST" action="{{ route('admin.users.update', $member) }}" style="max-width:520px">
    @csrf
    <label>Status
      <select name="status">
        @foreach (config('marketplace.user_statuses') as $value => $label)
          <option value="{{ $value }}" @selected($member->status === $value)>{{ $label }}</option>
        @endforeach
      </select>
    </label>
    <label>Reason if rejected<textarea name="rejection_reason">{{ $member->rejection_reason }}</textarea></label>
    <button class="btn">Update desk</button>
  </form>
  <form class="panel stack" method="POST" action="{{ route('admin.users.credit', $member) }}" style="max-width:520px">
    @csrf
    <h2>Credit wallet</h2>
    <label>Amount<input type="number" step="0.01" name="amount" required></label>
    <label>Narration<input name="narration" value="Operator funding" required></label>
    <button class="btn gold">Credit</button>
  </form>
  <h2>Documents</h2>
  @foreach ($member->documents as $document)
    <p><a href="{{ asset('storage/'.$document->path) }}" target="_blank">{{ $document->original_name }}</a> · {{ config('marketplace.document_types.'.$document->type) }} · @include('partials.status', ['status' => $document->status, 'group' => 'user_statuses'])</p>
  @endforeach
@endsection
