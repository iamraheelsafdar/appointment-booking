@extends('backend.layouts.app')
@section('title', 'Add User')
@section('backend')
    <div class="mw-100 login-card ">

        <form id="loginForm" action="{{ route('updateSetting') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-floating">
                <input type="text" class="form-control" id="title" name="title" placeholder="Enter site title"
                       required value="{{ $siteSetting->title ?? old('title') }}">
                <label for="title"><i class="fas fa-user-alt me-2"></i>Site Title</label>
            </div>

            <div class="form-floating">
                <input type="text" class="form-control" id="copy-right" name="copyright" placeholder="Enter copy right"
                       required value="{{ $siteSetting->copyright ?? old('copyright') }}">
                <label for="copy-right"><i class="fas fa-copyright me-2"></i>Site Copy Right</label>
            </div>

            <div class="mb-3">
                <label for="logo">Site Logo</label>
                <div class="d-flex align-items-center">
                    <img id="logoPreview"
                         src="{{ isset($siteSetting) && $siteSetting->logo ? asset('storage/' . $siteSetting->logo) : asset('assets/img/defaultLogo.png') }}"
                         alt="Logo Image Preview"
                         style="max-width: 46px; height: 40px"
                         class="img-thumbnail">
                    <input type="file" name="logo" id="logo" class="form-control">
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-login primary">
                <i class="fas fa-upload me-2"></i>Update Site Settings
            </button>
        </form>
    </div>
@endsection
