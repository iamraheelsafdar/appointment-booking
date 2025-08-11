@extends('backend.layouts.app')
@section('title', 'Update Appointment')
@section('backend')
    <div class="mw-100 login-card ">

        <form id="loginForm" action="{{ route('updateAppointments') }}" method="POST">
            @csrf
            <input type="hidden" name="id" value="{{ $appointment['id'] }}">
            <div class="form-floating">
                <input type="text" class="form-control" id="name" name="name" placeholder="Enter user name"
                       required disabled readonly value="{{ $appointment['name'] }}">
                <label for="name"><i class="fas fa-user-alt me-2"></i>User name</label>
            </div>

            <div class="form-floating">
                <input type="email" class="form-control" id="email" name="email" placeholder="Enter user email"
                       disabled readonly autocomplete="email" value="{{ $appointment['email'] }}">
                <label for="email"><i class="fas fa-envelope me-2"></i>Email Address</label>
            </div>
            <div class="form-floating">
                <select class="form-control" aria-label="Default select example" id="status"
                        name="appointment_status">
                    <option selected>{{$appointment['appointment_status']}}</option>
                    <option value="Pending">Pending</option>
                    <option value="Rejected">Rejected</option>
                    <option value="Declined">Declined</option>
                    <option value="Confirmed">Confirmed</option>
                </select>
                <label for="status"><i class="fas fa-arrow-up me-2"></i>Update Status</label>
            </div>

            <div class="form-floating">
                <select class="form-control" aria-label="Default select example" id="assign_to"
                        name="assign_to">

                    <option value="{{$appointment->coach->id}}" selected>{{$appointment->coach->name ?? ''}}</option>
                    @foreach($users as $name => $id)
                        <option value="{{$id}}">{{$name}}</option>
                    @endforeach
                </select>
                <label for="assign_to"><i class="fas fa-tasks me-2"></i>Assign appointment to</label>
            </div>

            <button type="submit" class="btn btn-primary btn-login primary">
                <i class="fas fa-upload me-2"></i>Update Appointment
            </button>
        </form>
    </div>
@endsection
