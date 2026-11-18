@extends('layouts.app')
@section('title', 'Members')
@section('content')
  <h1>Members</h1>
  <form class="filters" method="GET">
    <label>Role<select name="role"><option value="">All</option>@foreach(config('marketplace.roles') as $value => $label)<option value="{{ $value }}" @selected(request('role') === $value)>{{ $label }}</option>@endforeach</select></label>
    <label>Status<select name="status"><option value="">All</option>@foreach(config('marketplace.user_statuses') as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach</select></label>
    <button class="btn small">Filter</button>
  </form>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Desk</th><th>Role</th><th>State</th><th>Status</th></tr></thead>
      <tbody>
        @foreach ($users as $member)
          <tr>
            <td><a href="{{ route('admin.users.show', $member) }}">{{ $member->deskName() }}</a><br><span class="meta">{{ $member->email }}</span></td>
            <td>{{ role_label($member->role) }}</td>
            <td>{{ $member->state }}</td>
            <td>@include('partials.status', ['status' => $member->status, 'group' => 'user_statuses'])</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  {{ $users->links() }}
@endsection
