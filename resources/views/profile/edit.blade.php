@extends('layouts.app')
@section('title', 'Profile')
@section('content')
  <h1>{{ $user->deskName() }}</h1>
  <p>@include('partials.status', ['status' => $user->status, 'group' => 'user_statuses']) · {{ role_label($user->role) }}</p>
  <p class="meta">{{ $user->email }} · {{ $user->phone }} · {{ $user->city }}, {{ $user->state }}</p>
  <p><a href="{{ route('onboarding') }}">Update business details and documents</a></p>
  <form class="panel stack" method="POST" action="{{ route('profile.password') }}" style="max-width:420px">
    @csrf
    <h2>Password</h2>
    <label>Current<input type="password" name="current_password" required></label>
    <label>New<input type="password" name="password" required></label>
    <label>Confirm<input type="password" name="password_confirmation" required></label>
    <button class="btn">Update password</button>
  </form>
@endsection
