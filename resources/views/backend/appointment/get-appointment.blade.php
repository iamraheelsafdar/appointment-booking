@extends('backend.layouts.app')
@section('title', 'Appointment')
@section('backend')
    <form method="GET" action="{{ url()->current() }}" class="row g-3 mb-4">
        <div class="col-md-6">
            <label for="start_date">Select appointment starting date</label>
            <input id="start_date" type="date" name="start_date" class="form-control"
                   placeholder="Select appointment starting date"
                   value="{{ request()->get('start_date') }}">
        </div>
        <div class="col-md-6">
            <label for="end_date">Select appointment ending date</label>
            <input id="end_date" type="date" name="end_date" class="form-control"
                   placeholder="Select appointment ending date"
                   value="{{ request()->get('end_date') }}">
        </div>
        <div class="col-md-auto d-flex mx-auto">

            <button type="submit" class="btn btn-primary button me-2">Search</button>
            <a href="{{ url()->current() }}" class="btn btn-secondary button">Clear</a>
        </div>
    </form>
    <div class="table-responsive">
        {{-- Appointment Table --}}
        <table class="table table-bordered table-striped table-hover text-nowrap align-middle">
            <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Suburb</th>
                <th>Address</th>
                <th>City</th>
                <th>Country</th>
                <th>Postal Code</th>
                <th>State</th>
                <th>Appointment Status</th>
                <th>Total Minutes</th>
                <th>Total Amount</th>
                <th>Selected Date</th>
                <th>Selected Time Slot</th>
                @if(auth()->user()->user_type == 'Admin')
                    <th>Action</th>
                @endif
            </tr>
            </thead>
            <tbody>
            @forelse ($appointments['data'] as $key => $appointment)
                <tr>
                    <td>{{ ($appointments['current_page'] - 1) * $appointments['per_page'] + $key + 1 }}</td>
                    <td>{{ $appointment['name'] }}</td>
                    <td>{{ $appointment['email'] }}</td>
                    <td>{{ $appointment['suburb'] }}</td>
                    <td>{{ $appointment['address'] }}</td>
                    <td>{{ $appointment['city'] }}</td>
                    <td>{{ $appointment['country'] }}</td>
                    <td>{{ $appointment['postal_code'] }}</td>
                    <td>{{ $appointment['state'] }}</td>
                    <td>
                        <span class="text-white {{$appointment['appointment_status'] == 'Rejected' ? 'badge bg-warning' :
                                                      ($appointment['appointment_status'] == 'Declined' ? 'badge bg-danger' :
                                                      ($appointment['appointment_status'] == 'Confirmed' ? 'badge bg-success' : 'badge bg-info'))}}">

                        {{ $appointment['appointment_status'] }}
                        </span>
                    </td>
                    <td>{{ $appointment['total_minutes'] }}</td>
                    <td>{{ $appointment['total_amount'] }}</td>
                    <td>{{ $appointment['selected_date'] }}</td>
                    <td>{{ $appointment['selected_time_slot'] }}</td>


                    @if(auth()->user()->user_type == 'Admin')
                        <td class="d-flex justify-content-between">
                            <a href="{{ route('updateAppointmentsView', $appointment['id']) }}"
                               class="btn btn-sm btn-primary float-start"><i class="fa fa-pencil-alt"></i></a>
                            @if(isset($appointment['transaction']->session_id))
                                <a href="{{ route('transactionView', ['search' => $appointment['transaction']->session_id]) }}"
                                   class="btn btn-sm btn-info float-end ms-2"><i class="fa fa-cash-register"></i> View
                                    Transaction</a>
                            @endif
                        </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="15" class="text-center">No users found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @include('backend.layouts.pagination', ['dataToPaginate' => $appointments])
@endsection
