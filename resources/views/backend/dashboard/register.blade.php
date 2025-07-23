@extends('backend.layouts.app')
@section('title', 'Add User')
@section('backend')
    <div class="mw-100 login-card ">

        <form id="loginForm" action="{{ route('register') }}" method="POST">
            @csrf

            <div class="form-floating">
                <input type="text" class="form-control" id="name" name="name" placeholder="Enter user name"
                       required value="{{ old('name') }}">
                <label for="name"><i class="fas fa-user-alt me-2"></i>User name</label>
            </div>

            <div class="form-floating">
                <input type="email" class="form-control" id="email" name="email" placeholder="Enter user email"
                       required autocomplete="email" value="{{ old('email') }}">
                <label for="email"><i class="fas fa-envelope me-2"></i>Email Address</label>
            </div>

            <button type="submit" class="btn btn-primary btn-login">
                <i class="fas fa-plus-square me-2"></i>Add User
            </button>
        </form>
    </div>
@endsection
