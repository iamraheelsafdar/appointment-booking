<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tennis Lesson Booking - Enhanced</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <link rel="stylesheet" href="{{asset('assets/css/front-style.css')}}">
</head>
<body>
<!-- Toast Container -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999">
    <div id="liveToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header d-flex align-items-center">
            <i class="fas fa-circle me-2 toast-icon"></i>
            <strong class="me-auto toast-title">Notification</strong>
            <button type="button" class="btn-close ms-2" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body toast-message"></div>
    </div>
</div>

<div class="main-container">
    <!-- Calendar Header -->
    <div class="calendar-header">
        <h1 class="calendar-title" id="currentMonth">March 2024</h1>
        <div class="calendar-nav">
            <button class="today-btn" onclick="goToToday()">Today</button>
            <button class="nav-btn" onclick="previousMonth()">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="nav-btn" onclick="nextMonth()">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    </div>
    <div class="row">
        <div class="col-12 col-md-6 col-lg-6">
            <!-- Calendar Grid -->
            <div class="calendar-container">
                <div class="calendar-weekdays">
                    <div class="weekday">Mon</div>
                    <div class="weekday">Tue</div>
                    <div class="weekday">Wed</div>
                    <div class="weekday">Thu</div>
                    <div class="weekday">Fri</div>
                    <div class="weekday">Sat</div>
                    <div class="weekday">Sun</div>
                </div>
                <div class="calendar-dates" id="calendarDates">
                    <!-- Calendar dates will be populated by JavaScript -->
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-6">
            <!-- Time Selection Panel -->
            <div class="time-selection-panel" id="timePanel" style="display: none;">
                <div class="time-panel-header">
                    Available times for <span class="selected-date-chip" id="selectedDateChip"></span>
                </div>
                <div class="time-slots-grid" id="timeSlots">
                    <!-- Time slots will be populated by JavaScript -->
                </div>
            </div>
        </div>
    </div>


    <!-- No Selection Message -->
    <div class="no-selection" id="noSelection">
        <i class="far fa-calendar-alt"></i>
        <p>Select a date from the calendar above to view available time slots</p>
    </div>

    <!-- Stepper Form -->
    <div class="stepper-container" id="stepperForm" style="display: none;">
        <div class="stepper-header">
            <h2>Tennis Lesson Booking</h2>
        </div>

        <div class="stepper-content">
            <!-- Stepper Navigation -->
            <div class="stepper-steps">
                <div class="stepper-line">
                    <div class="stepper-line-progress" id="stepperProgress"></div>
                </div>
                <div class="stepper-step">
                    <div class="step-circle active" data-step="1">1</div>
                    <div class="step-title active">Personal & Lesson Details</div>
                </div>
                <div class="stepper-step">
                    <div class="step-circle" data-step="2">2</div>
                    <div class="step-title">Review & Confirm</div>
                </div>
            </div>

            <!-- Step 1: Personal & Lesson Details -->
            <div id="step1" class="step-content active">
                <!-- Customer Details Section -->
                <div class="customer-details">
                    <div class="customer-header">
                        <i class="fas fa-user"></i>
                        <h3>Personal Information</h3>
                    </div>

                    <div class="customer-grid">
                        <div class="form-group">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="fullName" placeholder="John Smith">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" placeholder="john@example.com">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" id="description"
                                      placeholder="Description"
                                      rows="1"></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Player Type</label>
                            <select class="form-control" id="playerType" onchange="onPlayerTypeChange(this.value)">
                                <option value="Returning">Returning Player</option>
                                <option value="FreeTrial">Free Trial Player</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Suburb</label>
                            <select class="form-control" id="suburb">
                                <option value="">Select Suburb</option>
                                <option value="Toorak">Toorak</option>
                                <option value="Malvern">Malvern</option>
                                <option value="Malvern East">Malvern East</option>
                                <option value="Ashburton">Ashburton</option>
                                <option value="Caulfield North">Caulfield North</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Address</label>
{{--                            <input type="text" class="form-control" id="address" placeholder="123 Main Street">--}}
                            <input type="text" class="form-control" id="address"
                                   placeholder="123 Main Street" autocomplete="off">
                        </div>

                        <div class="form-group">
                            <label class="form-label">City</label>
                            <input type="text" class="form-control" id="city" placeholder="Melbourne">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Postal Code</label>
                            <input type="text" class="form-control" id="postalCode" placeholder="4500">
                        </div>

                        <div class="form-group">
                            <label class="form-label">State</label>
                            <input type="text" class="form-control" id="state" placeholder="Victoria">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Country</label>
                            <input type="text" class="form-control" id="country" placeholder="Australia">
                        </div>
                    </div>
                </div>

                <!-- Lessons Section -->
                <div id="lessonsContainer">
                    <!-- Lessons will be dynamically added here -->
                </div>

                <button type="button" id="addLessonBtn" class="add-lesson-btn" onclick="addNewLesson()">
                    <i class="fas fa-plus"></i> Add Another Lesson
                </button>
            </div>

            <!-- Step 2: Review & Confirm -->
            <div id="step2" class="step-content">
                <div class="booking-summary">
                    <div class="summary-title">
                        <i class="fas fa-calendar-check"></i> Booking Summary
                    </div>
                    <div id="summaryContent">
                        <!-- Summary will be populated by JavaScript -->
                    </div>
                    <div class="total-price" id="summaryTotalPrice">
                        Total: $0.00
                    </div>
                </div>
            </div>

            <!-- Stepper Actions -->
            <div class="stepper-actions">
                <button type="button" class="btn-secondary" id="prevBtn" onclick="previousStep()"
                        style="display: none;">
                    <i class="fas fa-arrow-left"></i> Previous
                </button>
                <button type="button" class="btn-primary" id="nextBtn" onclick="nextStep()">
                    Next <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>

        <!-- Hidden fields for backend -->
        <input type="hidden" id="selectedTimeSlot" name="selected_time_slot" value="">
        <input type="hidden" id="totalMinutes" name="total_minutes" value="">
        <input type="hidden" id="bookingTotalPrice" name="booking_total_price" value="">
        <input type="hidden" id="bookingSummary" name="booking_summary" value="">
        <input type="hidden" id="totalAmount" name="total_amount" value="">
    </div>
</div>

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBLg9F5LgFPTIpvSwRasji1V31Grnc02L0&libraries=places"></script>

<script>
    window.bookedSlots = @json($bookedSlots);
    window.availablity = @json($availablity);
    window.slotDifference = @json((int) $siteSetting->slot_difference ?? 30);
</script>
<script src="{{asset('assets/js/front-script.js')}}"></script>
</body>
</html>
