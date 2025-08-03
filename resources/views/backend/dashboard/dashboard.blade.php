@extends('backend.layouts.app')
@section('title', 'Dashboard')
@section('backend')
    <div class="row ">
        <div class="col-xl-3 col-sm-6 col-12">
            <div class="card ">
                <div class="container mt-3">
                    <i class="fa fa-user"></i>
                </div>
                <div class="card-body">
                    <h5 class="card-title ms-1">Totol Number Of Users</h5>
                    <p class="card-text mb-5 ms-1">{{$total['user_count']}}</p>

                    <a href="{{route('getUser')}}"
                       class="button mb-1 mt-1 ">Go now</a>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 col-12">
            <div class="card ">
                <div class="container mt-3">
                    <i class="fa fa-copy"></i>
                </div>
                <div class="card-body">
                    <h5 class="card-title ms-1">Total Appointments</h5>
                    <p class="card-text mb-5 ms-1">5</p>

                    <a href="{{route('redirectToGoogle')}}" target="_blank"
                       class="button mb-1 mt-1 ">google</a>

                    <a href="https://freesnippetcode.blogspot.com/" target="_blank"
                       class="button mb-1 mt-1 ">Go now</a>
                    <a href="https://freesnippetcode.blogspot.com/" target="_blank"
                       class="button mb-1 mt-1 ">Go now</a>
                </div>
            </div>
        </div>
    </div>
@endsection
