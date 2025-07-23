@extends('backend.layouts.app')
@section('title', 'Login')
@section('backend')
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h2><i class="fas fa-lock me-2"></i>Welcome Back</h2>
                <p>Sign in to your account</p>
            </div>

            <form id="loginForam" action="{{ route('login') }}" method="POST">
                @csrf
                <div class="form-floating">
                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email"
                           required autocomplete="email" value="{{ old('email') }}">
                    <label for="email"><i class="fas fa-envelope me-2"></i>Email Address</label>
                </div>

                <div class="form-floating password-field">
                    <input type="password" class="form-control" id="password" name="password"
                           placeholder="Enter your password" required>
                    <label for="password"><i class="fas fa-key me-2"></i>Password</label>
                    <span class="password-toggle" onclick="togglePassword('password')">
                        <i class="fas fa-eye" id="password-eye"></i>
                    </span>
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="remember" name="remember_me" value="true">
                    <label class="form-check-label" for="remember">
                        Remember me
                    </label>
                </div>

                <button type="submit" class="btn btn-primary btn-login w-100">
                    <i class="fas fa-sign-in-alt me-2"></i>Sign In
                </button>
            </form>

            <div class="forgot-password">
                <a href="{{route('forgetPassword')}}">Forgot your password?</a>
            </div>
        </div>
    </div>
@endsection
