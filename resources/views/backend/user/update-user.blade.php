@extends('backend.layouts.app')
@section('title', 'Update User')
@section('backend')
    <div class="mw-100 login-card ">

        <form id="loginForm" action="{{ route('updateAppointments') }}" method="POST">
            @csrf
            <input type="hidden" name="id" value="{{ $user['id'] }}">
            <div class="form-floating">
                <input type="text" class="form-control" id="name" name="name" placeholder="Enter user name"
                       required value="{{ $user['name'] }}">
                <label for="name"><i class="fas fa-user-alt me-2"></i>User name</label>
            </div>

            <div class="form-floating">
                <input type="email" class="form-control" id="email" name="email" placeholder="Enter user email"
                       disabled readonly autocomplete="email" value="{{ $user['email'] }}">
                <label for="email"><i class="fas fa-envelope me-2"></i>Email Address</label>
            </div>

            <div class="form-floating">
                <input type="tel" class="form-control" id="phone" name="phone" placeholder="Enter user phone number"
                       required autocomplete="phone" value="{{ $user['phone'] }}">
                <label for="phone"><i class="fas fa-phone me-2"></i>Phone Number</label>
            </div>
            <div class="form-floating">
                <select class="form-control" aria-label="Default select example" id="status"
                        name="status" {{$user['remember_token'] == null ? '' : 'disabled' }}>
                    <option selected>{{$user['status'] == 1 ? 'Active' : 'Inactive'}}</option>
                    <option value="0">Inactive</option>
                    <option value="1">Active</option>
                </select>
                <label for="status"><i class="fas fa-arrow-up me-2"></i>Update Status</label>
            </div>
            <button type="submit" class="btn btn-primary btn-login primary">
                <i class="fas fa-upload me-2"></i>Update User
            </button>
        </form>
    </div>
@endsection
