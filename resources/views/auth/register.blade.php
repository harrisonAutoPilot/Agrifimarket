@extends('layouts.public')
@section('title', 'Open an account')
@section('content')
  <div class="auth-wrap">
    <section class="auth-story">
      <a class="brand" href="{{ route('home') }}" style="color:#f6f1e6"><strong>AgrifiMarket</strong></a>
      <div>
        <h1>Tell us which side of the market you are on.</h1>
        <p>The desk stays closed until an operator has seen your documents.</p>
      </div>
      <p class="meta">Already registered? <a href="{{ route('login') }}">Sign in</a></p>
    </section>
    <section class="auth-panel">
      <form class="form-grid" method="POST" action="{{ route('register') }}">
        @csrf
        <label>Your name<input name="name" value="{{ old('name') }}" required></label>
        <label>Phone<input name="phone" value="{{ old('phone') }}" placeholder="08000000000" required></label>
        <label class="wide">Email<input type="email" name="email" value="{{ old('email') }}" required></label>
        <label>Password<input type="password" name="password" required></label>
        <label>Confirm password<input type="password" name="password_confirmation" required></label>
        <label>Role
          <select name="role" required>
            @foreach (config('marketplace.register_roles') as $value => $label)
              <option value="{{ $value }}" @selected(old('role') === $value)>{{ $label }}</option>
            @endforeach
          </select>
        </label>
        <label>Account
          <select name="account_type" required>
            @foreach (config('marketplace.account_types') as $value => $label)
              <option value="{{ $value }}" @selected(old('account_type') === $value)>{{ $label }}</option>
            @endforeach
          </select>
        </label>
        <label class="wide">Business name<input name="business_name" value="{{ old('business_name') }}"></label>
        <label>RC / CAC number<input name="rc_number" value="{{ old('rc_number') }}"></label>
        <label>State
          <select name="state" required>
            @foreach (config('marketplace.states') as $state)
              <option @selected(old('state') === $state)>{{ $state }}</option>
            @endforeach
          </select>
        </label>
        <label>City<input name="city" value="{{ old('city') }}" required></label>
        <div class="wide"><button class="btn" type="submit">Create desk</button></div>
      </form>
    </section>
  </div>
@endsection
