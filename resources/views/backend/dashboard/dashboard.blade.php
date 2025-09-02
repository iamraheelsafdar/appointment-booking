@extends('backend.layouts.app')
@section('title', 'Dashboard')
@section('backend')
    <div class="row ">
        @if(auth()->user()->user_type == 'Admin')
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card ">
                    <div class="container mt-3">
                        <i class="fa fa-user"></i>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title ms-1">Totol Number Of Coaches</h5>
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
                        <p class="card-text mb-5 ms-1">{{$total['appointment_count']}}</p>
                        <a href="{{route('appointmentsView')}}"
                           class="button mb-1 mt-1 ">Go now</a>
                    </div>
                </div>
            </div>
        @endif
        <div class="col-xl-3 col-sm-6 col-12">
            <div class="card ">
                <div class="container mt-3">
                    <i class="fa fa-calendar-alt"></i>
                </div>
                <div class="card-body">
                    @if(isset(auth()->user()->google->id_token))
                    <h5 class="card-title ms-1">Google Calender Is Connected</h5>
                    <p class="card-text mb-5 ms-1">Connected</p>
                    <a href="" style="cursor: not-allowed"
                       class="button bg-gradient mb-1 mt-1 ">Google Calender Connected</a>
                    @else()
                        <h5 class="card-title ms-1">Google Calender Is Not Connected</h5>
                        <p class="card-text mb-5 ms-1">Not Connected</p>
                        <a href="{{route('redirectToGoogle')}}" target="_self"
                           class="button mb-1 mt-1 ">Connect Google Calender</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
