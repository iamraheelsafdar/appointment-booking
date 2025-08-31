<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.2/css/all.css">
    <link rel="stylesheet" href="{{asset("assets/css/style.css")}}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard')</title>
    <link rel="shortcut icon" href="{{isset($siteSetting) && $siteSetting->logo ? asset('storage/' . $siteSetting->logo) : asset('assets/img/defaultLogo.png')}}">
</head>
<body>

@if(session('success'))
    <div class="toast-container">
        <div class="toast" role="alert" aria-live="assertive" aria-atomic="true"
             style="background-color: #d4edda; color: #155724;">
            <div class="toast-body text-dark">
                <button type="button" class="btn-close float-end d-flex justify-content-center"
                        data-bs-dismiss="toast" aria-label="Close"></button>
                {{ session('success') }}
            </div>
        </div>
    </div>
@endif
@if(session('errors'))
    <div class="toast-container">
        @php
            $errors = is_array(session('errors')) ? session('errors'): (array) session('errors');
        @endphp
        @foreach($errors as $error)
            <div class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-body">
                    <button type="button" class="btn-close float-end d-flex justify-content-center"
                            data-bs-dismiss="toast" aria-label="Close"></button>
                    {{ $error }}
                </div>
            </div>
        @endforeach
    </div>
@endif




@if(auth()->user())
    <div class="page-wrapper chiller-theme toggled">
        @include('backend.layouts.header')
        <main class="page-content position-relative h-100">
            <div class="container-fluid">
                <h2>@yield('title')</h2>
                <hr>
                <div class="row">
                    <div class="form-group col-md-12">
                        @yield('backend')
                    </div>
                </div>
            </div>
            @include('backend.layouts.footer')
        </main>
    </div>
@endif
@if(!auth()->user())
    @yield('backend')
@endif
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.6.0/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.min.js"></script>
<script src='{{asset("assets/js/script.js")}}'></script>
</body>
</html>
