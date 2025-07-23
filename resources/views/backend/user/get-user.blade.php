@extends('backend.layouts.app')
@section('title', 'Users')
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

    {{-- User Table --}}
    <table class="table table-bordered table-striped table-hover">
        <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>Name (Reference)</th>
            <th>Email (Status)</th>
            <th>Last Login</th>
            <th>Registration Date</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        @forelse ($users['data'] as $key => $user)
            <tr>
                <td>{{ ($users['current_page'] - 1) * $users['per_page'] + $key + 1 }}</td>
                <td>{{ $user['reference'] }}</td>
                <td>{{ $user['status'] }}</td>
                <td>{{ $user['last_login'] }}</td>
                <td>{{ $user['registration_date'] }}</td>
                <td>
                    {{--                    <a href="{{ route('user.edit', $user['id']) }}" class="btn btn-sm btn-primary">Edit</a>--}}
                    {{--                    <form action="{{ route('user.destroy', $user['id']) }}" method="POST" style="display:inline-block;"--}}
                    {{--                          onsubmit="return confirm('Are you sure to delete this user?')">--}}
                    {{--                        @csrf--}}
                    {{--                        @method('DELETE')--}}
                    {{--                        <button class="btn btn-sm btn-danger">Delete</button>--}}
                    {{--                    </form>--}}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center">No users found.</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    @include('backend.layouts.pagination', ['dataToPaginate' => $users])
@endsection
