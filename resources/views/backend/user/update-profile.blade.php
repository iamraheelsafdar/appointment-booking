@extends('backend.layouts.app')
@section('title', 'Update Profile')
@section('backend')
    <div class="mw-100 login-card">

        <form id="loginForm" action="{{ route('updateProfile') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-6">
                    <div class="mb-3 form-floating">
                        <input type="text" class="form-control" id="name" name="name" placeholder="Enter user name"
                               required value="{{ $user['name'] }}">
                        <label for="name"><i class="fas fa-user-alt me-2"></i>User name</label>
                    </div>
                </div>
                <div class="col-6">
                    <div class="mb-3 form-floating">
                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter user email"
                               disabled readonly autocomplete="email" value="{{ $user['email'] }}">
                        <label for="email"><i class="fas fa-envelope me-2"></i>Email Address</label>
                    </div>
                </div>
                <div class="col-6">
                    <div class="mb-3 form-floating">
                        <input type="tel" class="form-control" id="phone" name="phone"
                               placeholder="Enter user phone number"
                               required autocomplete="phone" value="{{ $user['phone'] }}">
                        <label for="phone"><i class="fas fa-phone me-2"></i>Phone Number</label>
                    </div>
                </div>
                <div class="col-6">
                    <div class="mb-3">
                        <label for="logo">Profile Image</label>
                        <div class="d-flex align-items-center">
                            <img id="logoPreview"
                                 src="{{ isset($user) && $user->profile_image ? asset('storage/' . $user->profile_image) : asset('assets/img/profileImage.png') }}"
                                 alt="Profile Image Preview"
                                 style="max-width: 46px; height: 40px"
                                 class="img-thumbnail">
                            <input type="file" name="profile_image" id="logo" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-floating password-field">
                        <input type="password" class="form-control" id="new_password" name="new_password"
                               placeholder="Enter your password">
                        <label for="new_password"><i class="fas fa-key me-2"></i>New Password</label>
                        <span class="password-toggle" onclick="togglePassword('new_password')">
                        <i class="fas fa-eye" id="password-eye"></i>
                    </span>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-floating password-field mb-3">
                        <input type="password" class="form-control" id="old_password" name="old_password"
                               placeholder="Enter your password">
                        <label for="old_password"><i class="fas fa-key me-2"></i>Old Password</label>
                        <span class="password-toggle" onclick="togglePassword('old_password')">
                        <i class="fas fa-eye" id="password-eye"></i>
                    </span>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-login primary">
                <i class="fas fa-upload me-2"></i>Update Profile
            </button>
        </form>
    </div>
@endsection
