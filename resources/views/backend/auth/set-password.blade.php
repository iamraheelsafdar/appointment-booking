@extends('backend.layouts.app')
@section('title', 'Password Reset')
@section('backend')
    <div class="reset-container">
        <div class="reset-card">
            <div class="reset-header">
                <h4>Please reset your Password</h4>
            </div>

            <form method="POST" action="{{route('setPassword')}}">
                @csrf
                <input type="hidden" name="token" id="" value="{{$token}}">
                <input type="hidden" name="email" id="" value="{{$email}}">
                <div class="form-floating password-field">
                    <input type="password" class="form-control" id="password" name="password"
                           placeholder="Create a password" required>
                    <label for="password"><i class="fas fa-key me-2"></i>Password</label>
                    <span class="password-toggle" onclick="togglePassword('password')">
                                        <i class="fas fa-eye" id="password-eye"></i>
                                    </span>
                </div>

                <div class="form-floating password-field">
                    <input type="password" class="form-control" id="confirmPassword" name="password_confirmation"
                           placeholder="Confirm your password" required>
                    <label for="confirmPassword"><i class="fas fa-lock me-2"></i>Confirm Password</label>
                    <span class="password-toggle" onclick="togglePassword('confirmPassword')">
                                        <i class="fas fa-eye" id="confirmPassword-eye"></i>
                                    </span>
                </div>

                <button type="submit" class="btn btn-primary btn-reset w-100" id="submitBtn">
                    <i class="fas fa-key me-2"></i>Set Password
                </button>
            </form>
        </div>
    </div>
@endsection
