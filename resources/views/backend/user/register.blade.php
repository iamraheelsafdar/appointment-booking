@extends('backend.layouts.app')
@section('title', 'Add Coach')
@section('backend')
    <div class="mw-100 login-card ">

        <form id="loginForm" action="{{ route('register') }}" method="POST">
            @csrf

            <div class="form-floating">
                <input type="text" class="form-control" id="name" name="name" placeholder="Enter coach name"
                       required value="{{ old('name') }}">
                <label for="name"><i class="fas fa-user-alt me-2"></i>User name</label>
            </div>

            <div class="form-floating">
                <input type="email" class="form-control" id="email" name="email" placeholder="Enter coach email"
                       required autocomplete="email" value="{{ old('email') }}">
                <label for="email"><i class="fas fa-envelope me-2"></i>Email Address</label>
            </div>

            <div class="form-floating">
                <input type="tel" class="form-control" id="phone" name="phone" placeholder="Enter coach phone number"
                       required autocomplete="phone" value="{{ old('phone') }}">
                <label for="phone"><i class="fas fa-phone me-2"></i>Phone Number</label>
            </div>

            <div class="form-floating">
                <select class="form-control" aria-label="Default select example" id="coach_type"
                        name="coach_type">
                    <option value="Normal Coach">Normal Coach</option>
                    <option value="High Level Coach">High Level Coach</option>
                </select>
                <label for="coach_type"><i class="fas fa-arrow-up me-2"></i>Coach Type</label>
            </div>

            <button type="submit" class="btn btn-primary btn-login">
                <i class="fas fa-plus-square me-2"></i>Add Coach
            </button>
        </form>
    </div>
@endsection
