@extends('layouts.app')
@section('title', 'Documents')
@section('content')
  <p class="kicker">Onboarding</p>
  <h1>Open your desk.</h1>
  <p>Status: @include('partials.status', ['status' => $user->status, 'group' => 'user_statuses'])</p>
  @if ($user->rejection_reason)<p>{{ $user->rejection_reason }}</p>@endif
  <div class="two" style="grid-template-columns: 1fr 1fr">
    <form class="panel stack" method="POST" action="{{ route('onboarding.update') }}">
      @csrf
      <h2>Business details</h2>
      <label>Business name<input name="business_name" value="{{ old('business_name', $user->business_name) }}"></label>
      <label>RC / CAC<input name="rc_number" value="{{ old('rc_number', $user->rc_number) }}"></label>
      <label>NIN<input name="nin" value="{{ old('nin') }}" inputmode="numeric" maxlength="11"></label>
      <label>BVN<input name="bvn" value="{{ old('bvn') }}" inputmode="numeric" maxlength="11"></label>
      <label>State
        <select name="state">
          @foreach (config('marketplace.states') as $state)
            <option @selected(old('state', $user->state) === $state)>{{ $state }}</option>
          @endforeach
        </select>
      </label>
      <label>City<input name="city" value="{{ old('city', $user->city) }}" required></label>
      <label>Address<input name="address" value="{{ old('address', $user->address) }}"></label>
      <label>Bank<input name="bank_name" value="{{ old('bank_name', $user->bank_name) }}"></label>
      <label>Account name<input name="bank_account_name" value="{{ old('bank_account_name', $user->bank_account_name) }}"></label>
      <label>Account number<input name="bank_account_number" value="{{ old('bank_account_number', $user->bank_account_number) }}"></label>
      <label>Guarantor<input name="guarantor_name" value="{{ old('guarantor_name', $user->guarantor_name) }}"></label>
      <label>Guarantor phone<input name="guarantor_phone" value="{{ old('guarantor_phone', $user->guarantor_phone) }}"></label>
      <label>About the desk<textarea name="bio">{{ old('bio', $user->bio) }}</textarea></label>
      <button class="btn">Save details</button>
      <p class="meta">NIN and BVN are stored for review and are not printed on public pages. Leave them blank to keep the current values.</p>
    </form>
    <div>
      <form class="panel stack" method="POST" action="{{ route('onboarding.documents') }}" enctype="multipart/form-data">
        @csrf
        <h2>Upload a document</h2>
        <label>Type
          <select name="type" required>
            @foreach (config('marketplace.document_types') as $value => $label)
              <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
          </select>
        </label>
        <label>File<input type="file" name="document" accept=".jpg,.jpeg,.png,.pdf" required></label>
        <button class="btn">Upload</button>
      </form>
      <div class="panel" style="margin-top:1rem">
        <h2>Received</h2>
        @forelse ($documents as $document)
          <p class="split">{{ config('marketplace.document_types.'.$document->type) }} <span>@include('partials.status', ['status' => $document->status, 'group' => 'user_statuses'])</span></p>
        @empty
          <p class="meta">Nothing uploaded yet.</p>
        @endforelse
      </div>
    </div>
  </div>
@endsection
