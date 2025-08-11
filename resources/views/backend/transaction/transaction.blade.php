@extends('backend.layouts.app')
@section('title', 'Transactions')
@section('backend')
    <form method="GET" action="{{ url()->current() }}" class="row g-3 mb-4">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Search by session id"
                   value="{{ request()->get('search') }}">
        </div>
        <div class="col-md-auto d-flex">
            <button type="submit" class="btn btn-primary button me-2">Search</button>
            <a href="{{ url()->current() }}" class="btn btn-secondary button">Clear</a>
        </div>
    </form>
    <div class="table-responsive">
        {{-- Transaction Table --}}
        <table class="table table-bordered table-striped table-hover text-nowrap align-middle">
            <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Session Id</th>
                <th>Type</th>
                <th>Status</th>
                <th>Amount</th>
                <th>Description</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($transactions['data'] as $key => $transaction)
                <tr>
                    <td>{{ ($transactions['current_page'] - 1) * $transactions['per_page'] + $key + 1 }}</td>
                    <td>{{ $transaction['session_id'] }}</td>
                    <td>{{ $transaction['type'] }}</td>
                    <td class="text-uppercase">
                    <span class="badge {{ $transaction['status'] == 'SUCCESS' ? 'bg-success' : 'bg-danger'}} ">
                        {{ $transaction['status'] }}
                    </span>
                    </td>
                    <td>{{ $transaction['amount'] }} {{$transaction['currency']}}</td>
                    <td style="white-space: break-spaces;height: 100px;display: block;overflow: hidden;overflow-y: scroll;">
                        {{ $transaction['description'] }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">No users found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @include('backend.layouts.pagination', ['dataToPaginate' => $transactions])
@endsection
