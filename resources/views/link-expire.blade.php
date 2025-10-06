@extends('backend.layouts.app')
@section('title', 'Link Expired')

@section('backend')
    <section class="d-flex align-items-center min-vh-100 py-5">
        <div class="container py-5">
            <div class="row align-items-center">
                {{-- Image Section --}}
                <div class="col-md-6 order-md-2 text-center">
                    <div class="lc-block">
                        <img src="{{asset('assets/img/expire.png')}}" alt="Link Expired" class="img-fluid">
                    </div>
                </div>

                {{-- Text Section --}}
                <div class="col-md-6 text-center text-md-start">
                    <div class="lc-block mb-3">
                        <h1 class="display-1 fw-bold text-muted">Link Expired</h1>
                    </div>

                    <div class="lc-block mb-4">
                        <p class="rfs-11 fw-light">
                            The link you followed has expired or is no longer valid. <br>
                            If you were trying to set your password or activate your account, please request a new invitation or contact support.
                        </p>
                    </div>

                    <div class="lc-block">
                        <a class="btn btn-lg btn-secondary me-2" href="{{ route('login') }}">Back to Login</a>
                        <a class="btn btn-outline-dark mt-2 mt-md-0" href="mailto:info@homecourtadvantage.net">Contact Support</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
