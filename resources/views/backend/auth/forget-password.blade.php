@extends('backend.layouts.app')
@section('title', 'Password Reset')
@section('backend')
    <div class="forget-container">
        <div class="forget-card">
            <div class="forget-header">
                <h4>Please enter your Email</h4>
            </div>

            <form >
                <div class="form-floating">
                    <input type="email" autocomplete="email" class="form-control" id="email" name="email"
                           placeholder="Enter your email" required>
                    <label for="email"><i class="fas fa-envelope me-2"></i>Enter Email</label>
                </div>

                <button type="submit" class="btn btn-primary btn-forget w-100" id="submitBtn">
                    <i class="fas fa-key me-2"></i>Forget Password
                </button>
            </form>
            <div class="back-login">
                <a href="{{route('loginView')}}">Back to login</a>
            </div>
        </div>
    </div>
@endsection
