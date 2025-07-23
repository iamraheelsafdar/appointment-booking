@extends('backend.layouts.app')
@section('title', '404 Not Found')
@section('backend')
    <section class="d-flex align-items-center min-vh-100 py-5">
        <div class="container py-5">
            <div class="row align-items-center">
                <div class="col-md-6 order-md-2">
                    <div class="lc-block">
                        <img src="{{asset('assets/img/404.png')}}" alt="404 Expired" class="img-fluid">
                    </div>
                </div>
                <div class="col-md-6 text-center text-md-start ">
                    <div class="lc-block mb-3">
                        <div>
                            <h1 class="display-1 fw-bold text-muted">Error 404</h1>
                        </div>
                    </div>
                    <div class="lc-block mb-5">
                        <div>
                            <p class="rfs-11 fw-light"> The page you are looking for was moved, removed or might never
                                existed.</p>
                        </div>
                    </div>
                    <div class="lc-block">
                        <a class="btn btn-lg btn-secondary" href="{{route('login')}}" role="button">Back to homepage</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
