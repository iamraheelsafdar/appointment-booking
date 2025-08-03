@extends('backend.layouts.app')
@section('title', 'Set Availability')
@section('backend')
    <div class="mw-100 login-card ">

        <form id="loginForm" action="{{ route('createAvailability') }}" method="POST">
            @csrf
            <!-- Days and Time Inputs -->
            <div class="row">
                <!-- Repeat this block for each day -->
                <!-- Sunday -->
                <div class="col-md-6 mb-3">
                    <div class="form-check">
                        <input class="form-check-input day-checkbox" type="checkbox" id="SundayCheckbox" name="sunday" {{isset($detail['Sunday']) ? 'checked' : '' }}>
                        <label class="form-check-label" for="SundayCheckbox">Sunday</label>
                    </div>
                    <div class="row mt-2 time-inputs {{isset($detail['Sunday']) ? '' : 'd-none' }}" id="SundayTime">
                        <div class="col">
                            <label>Start Time</label>
                            <input type="time" class="form-control start-time" data-day="Sunday"
                                   name="sunday_start_time" value="{{$detail['Sunday']['start_time'] ?? '' }}">
                        </div>
                        <div class="col">
                            <label>End Time</label>
                            <input type="time" class="form-control end-time" data-day="Sunday" name="sunday_end_time" value="{{$detail['Sunday']['end_time'] ?? '' }}">
                        </div>
                    </div>
                </div>

                <!-- Monday -->
                <div class="col-md-6 mb-3">
                    <div class="form-check">
                        <input class="form-check-input day-checkbox" type="checkbox" id="MondayCheckbox" name="monday" {{isset($detail['Monday']) ? 'checked' : '' }}>
                        <label class="form-check-label" for="MondayCheckbox">Monday</label>
                    </div>
                    <div class="row mt-2 time-inputs {{isset($detail['Monday']) ? '' : 'd-none' }}" id="MondayTime">
                        <div class="col">
                            <label>Start Time</label>
                            <input type="time" class="form-control start-time" data-day="Monday"
                                   name="monday_start_time" value="{{$detail['Monday']['start_time'] ?? '' }}">
                        </div>
                        <div class="col">
                            <label>End Time</label>
                            <input type="time" class="form-control end-time" data-day="Monday" name="monday_end_time" value="{{$detail['Monday']['end_time'] ?? '' }}">
                        </div>
                    </div>
                </div>

                <!-- Tuesday -->
                <div class="col-md-6 mb-3">
                    <div class="form-check">
                        <input class="form-check-input day-checkbox" type="checkbox" id="TuesdayCheckbox"
                               name="tuesday" {{isset($detail['Tuesday']) ? 'checked' : '' }}>
                        <label class="form-check-label" for="TuesdayCheckbox">Tuesday</label>
                    </div>
                    <div class="row mt-2 time-inputs {{isset($detail['Tuesday']) ? '' : 'd-none' }}" id="TuesdayTime">
                        <div class="col">
                            <label>Start Time</label>
                            <input type="time" class="form-control start-time" data-day="Tuesday"
                                   name="tuesday_start_time" value="{{$detail['Tuesday']['start_time'] ?? '' }}">
                        </div>
                        <div class="col">
                            <label>End Time</label>
                            <input type="time" class="form-control end-time" data-day="Tuesday" name="tuesday_end_time" value="{{$detail['Tuesday']['end_time'] ?? '' }}">
                        </div>
                    </div>
                </div>

                <!-- Wednesday -->
                <div class="col-md-6 mb-3">
                    <div class="form-check">
                        <input class="form-check-input day-checkbox" type="checkbox" id="WednesdayCheckbox"
                               name="wednesday" {{isset($detail['Wednesday']) ? 'checked' : '' }}>
                        <label class="form-check-label" for="WednesdayCheckbox">Wednesday</label>
                    </div>
                    <div class="row mt-2 time-inputs {{isset($detail['Wednesday']) ? '' : 'd-none' }}" id="WednesdayTime">
                        <div class="col">
                            <label>Start Time</label>
                            <input type="time" class="form-control start-time" data-day="Wednesday"
                                   name="wednesday_start_time" value="{{$detail['Wednesday']['start_time'] ?? '' }}">
                        </div>
                        <div class="col">
                            <label>End Time</label>
                            <input type="time" class="form-control end-time" data-day="Wednesday"
                                   name="wednesday_end_time" value="{{$detail['Wednesday']['end_time'] ?? '' }}">
                        </div>
                    </div>
                </div>

                <!-- Thursday -->
                <div class="col-md-6 mb-3">
                    <div class="form-check">
                        <input class="form-check-input day-checkbox" type="checkbox" id="ThursdayCheckbox"
                               name="thursday" {{isset($detail['Thursday']) ? 'checked' : '' }}>
                        <label class="form-check-label" for="ThursdayCheckbox">Thursday</label>
                    </div>
                    <div class="row mt-2 time-inputs {{isset($detail['Thursday']) ? '' : 'd-none' }}" id="ThursdayTime">
                        <div class="col">
                            <label>Start Time</label>
                            <input type="time" class="form-control start-time" data-day="Thursday"
                                   name="thursday_start_time" value="{{$detail['Thursday']['start_time'] ?? '' }}">
                        </div>
                        <div class="col">
                            <label>End Time</label>
                            <input type="time" class="form-control end-time" data-day="Thursday"
                                   name="thursday_end_time" value="{{$detail['Thursday']['end_time'] ?? '' }}">
                        </div>
                    </div>
                </div>

                <!-- Friday -->
                <div class="col-md-6 mb-3">
                    <div class="form-check">
                        <input class="form-check-input day-checkbox" type="checkbox" id="FridayCheckbox" name="friday" {{isset($detail['Friday']) ? 'checked' : '' }}>
                        <label class="form-check-label" for="FridayCheckbox">Friday</label>
                    </div>
                    <div class="row mt-2 time-inputs {{isset($detail['Friday']) ? '' : 'd-none' }}" id="FridayTime">
                        <div class="col">
                            <label>Start Time</label>
                            <input type="time" class="form-control start-time" data-day="Friday"
                                   name="friday_start_time" value="{{$detail['Friday']['start_time'] ?? '' }}">
                        </div>
                        <div class="col">
                            <label>End Time</label>
                            <input type="time" class="form-control end-time" data-day="Friday" name="friday_end_time" value="{{$detail['Friday']['end_time'] ?? '' }}">
                        </div>
                    </div>
                </div>

                <!-- Saturday -->
                <div class="col-md-6 mb-3">
                    <div class="form-check">
                        <input class="form-check-input day-checkbox" type="checkbox" id="SaturdayCheckbox"
                               name="saturday" {{isset($detail['Saturday']) ? 'checked' : '' }}>
                        <label class="form-check-label" for="SaturdayCheckbox">Saturday</label>
                    </div>
                    <div class="row mt-2 time-inputs {{isset($detail['Saturday']) ? '' : 'd-none' }}" id="SaturdayTime">
                        <div class="col">
                            <label>Start Time</label>
                            <input type="time" class="form-control start-time" data-day="Saturday"
                                   name="saturday_start_time" value="{{$detail['Saturday']['start_time'] ?? '' }}">
                        </div>
                        <div class="col">
                            <label>End Time</label>
                            <input type="time" class="form-control end-time" data-day="Saturday"
                                   name="saturday_end_time" value="{{$detail['Saturday']['end_time'] ?? '' }}">
                        </div>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-login primary">
                <i class="fas fa-upload me-2"></i>Set Availability
            </button>
        </form>
    </div>
@endsection
