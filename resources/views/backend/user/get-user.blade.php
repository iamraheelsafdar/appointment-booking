@extends('backend.layouts.app')
@section('title', 'Coach')
@section('backend')
    <form method="GET" action="{{ url()->current() }}" class="row g-3 mb-4">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Search by name or email"
                   value="{{ request()->get('search') }}">
        </div>
        <div class="col-md-auto d-flex">
            <button type="submit" class="btn btn-primary button me-2">Search</button>
            <a href="{{ url()->current() }}" class="btn btn-secondary button">Clear</a>
        </div>
    </form>
    <div class="table-responsive">
        {{-- User Table --}}
        <table class="table table-bordered table-striped table-hover text-nowrap align-middle">
            <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Coach Type</th>
                <th>Phone</th>
                <th>Status</th>
                <th>Last Login</th>
                <th>Registration Date</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($users['data'] as $key => $user)
                <tr>
                    <td>{{ ($users['current_page'] - 1) * $users['per_page'] + $key + 1 }}</td>
                    <td>{{ $user['name'] }}</td>
                    <td>{{ $user['email'] }}</td>
                    <td>{{ $user['coach_type'] }}</td>
                    <td>{{ $user['phone'] }}</td>
                    <td class="text-uppercase">
                    <span class="badge {{ $user['status'] == 'active' ? 'bg-success' : 'bg-danger'}} ">
                        {{ $user['status'] }}
                    </span>
                    </td>
                    <td>{{ $user['last_login'] }}</td>
                    <td>{{ $user['registration_date'] }}</td>
                    <td>
                        <a href="{{ route('updateUserView', $user['id']) }}" class="btn btn-sm btn-primary float-start"><i
                                class="fa fa-pencil-alt"></i></a>
                        <form action="{{ route('deleteUser') }}" method="POST" style="display:inline-block;"
                              onsubmit="return confirm('Are you sure to delete this user?')">
                            @csrf
                            <input type="hidden" name="id" value="{{ $user['id'] }}"/>
                            <button class="btn btn-sm btn-danger danger ms-2"><i class="fa fa-trash-alt"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center">No users found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @include('backend.layouts.pagination', ['dataToPaginate' => $users])
@endsection
