<?php

namespace App\Services\Site;

use App\DTOs\Appointment\CreateAppointmentDTO;
use App\DTOs\Transaction\CreateTransactionDTO;
use App\Interfaces\Site\FrontEndInterface;
use App\Jobs\AppointmentBookingjob;
use App\Jobs\AppointmentReceivingjob;
use App\Models\SiteSettings;
use App\Models\User;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use App\DTOs\Lessons\CreateLessonsDTO;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Stripe\Checkout\Session;
use App\Models\Availability;
use App\Models\Appointment;
use App\Models\Transaction;
use Illuminate\Support\Str;
use App\Models\Lesson;
use Stripe\Stripe;
use Carbon\Carbon;
use App\Helper;
use Stripe\Webhook;

class FrontEndService implements FrontEndInterface
{

    /**
     * @return View|Application|Factory|\Illuminate\Contracts\Foundation\Application
     */
    public static function frontendView(): View|Application|Factory|\Illuminate\Contracts\Foundation\Application
    {


//        $appointments = Appointment::whereIn('appointment_status', ['Pending', 'Confirmed'])->get();
//        $bookedSlots = [];
//
//        foreach ($appointments as $appointment) {
//            $timePart = explode(',', $appointment->selected_time_slot)[0]; // e.g. "12:00 PM - 3:15 PM"
//            [$startTime, $endTime] = explode(' - ', $timePart);
//
//            // Convert to 24-hour format
//            $start = Carbon::createFromFormat('g:i A', trim($startTime))->format('H:i');
//            $end = Carbon::createFromFormat('g:i A', trim($endTime))->format('H:i');
//
//            // Generate 15-minute interval slots
//            $current = Carbon::createFromFormat('H:i', $start);
//            $endObj = Carbon::createFromFormat('H:i', $end);
//            $slots = [];
//
//            while ($current <= $endObj) {
//                $slots[] = $current->format('H:i');
//                $current->addMinutes(15);
//            }
//
//            // Group by selected_date
//            $date = $appointment->selected_date;
//            if (!isset($bookedSlots[$date])) {
//                $bookedSlots[$date] = [];
//            }
//
//            $bookedSlots[$date] = array_values(array_unique(array_merge($bookedSlots[$date], $slots)));
//        }

//        $availablities = Availability::where('is_active', 1)->get()->toArray();
//        $dayIndexes = [
//            'Sunday' => 0,
//            'Monday' => 1,
//            'Tuesday' => 2,
//            'Wednesday' => 3,
//            'Thursday' => 4,
//            'Friday' => 5,
//            'Saturday' => 6,
//        ];
//
//        $availablity = [];
//
//        foreach ($availablities as $value) {
//            $day = $value['day'];
//            if (isset($dayIndexes[$day])) {
//                $index = $dayIndexes[$day];
//                $availablity[$index] = [
//                    'startTime' => Carbon::createFromFormat('H:i:s', $value['start_time'])->format('H:i'),
//                    'endTime' => Carbon::createFromFormat('H:i:s', $value['end_time'])->format('H:i'),
//                ];
//            }
//        }
        // Get merged availability for backward compatibility
        $availability = Availability::getMergedAvailability();

        // Get individual coach availability - only coaches with Google Calendar connected
        $coaches = User::where('user_type', 'Coach')
            ->where('coach_type', 'Normal Coach')
            ->where('status', 1)
            ->whereHas('google') // Only coaches with Google Calendar connected
            ->get(['id', 'name']);

        $coachNames = $coaches->pluck('name')->toArray();

        // Get individual coach availability with buffer minutes
        $coachAvailability = [];
        foreach ($coaches as $coach) {
            $availabilityData = Availability::where('user_id', $coach->id)
                ->where('is_active', true)
                ->orderBy('day')
                ->orderBy('start_time')
                ->get();

            if ($availabilityData->count() > 0) {
                $availabilityByDay = $availabilityData
                    ->groupBy('day')
                    ->map(function($dayAvailabilities) {
                        return [
                            'chunks' => $dayAvailabilities->map(function($availability) {
                                return [
                                    'startTime' => $availability->start_time->format('H:i'),
                                    'endTime' => $availability->end_time->format('H:i'),
                                    'bufferMinutes' => $availability->buffer_minutes ?? 0,
                                ];
                            })->toArray()
                        ];
                    })
                    ->toArray();

                // Convert day names to day numbers for JavaScript compatibility
                $convertedAvailability = [];
                foreach ($availabilityByDay as $dayName => $dayData) {
                    $dayNumber = Availability::getDayNumber($dayName);
                    $convertedAvailability[$dayNumber] = $dayData;
                }

                $coachAvailability[$coach->id] = [
                    'id' => $coach->id,
                    'name' => $coach->name,
                    'availability' => $convertedAvailability
                ];
            }
        }

        // If no individual coach availability, create default availability
        if (empty($coachAvailability)) {
            // Create default availability for each coach based on merged availability
            $defaultAvailability = Availability::getMergedAvailability();
            foreach ($coaches as $coach) {
                $coachAvailability[$coach->id] = [
                    'id' => $coach->id,
                    'name' => $coach->name,
                    'availability' => $defaultAvailability,
                    'isDefault' => true // Flag to indicate this is default availability
                ];
            }
        }

        // Get actual booked slots from appointments with coach information
        $appointments = Appointment::whereIn('appointment_status', ['Confirmed', 'Pending'])
            ->whereDate('selected_date', '>=', Carbon::today())
            ->with('coach')
            ->get();

        $bookedSlots = [];
        $siteSettings = SiteSettings::first();
        $bufferMinutes = $siteSettings->buffer_minutes ?? 0;

        foreach ($appointments as $appointment) {
            $date = $appointment->selected_date;
            $timeSlot = $appointment->selected_time_slot;
            $coachId = $appointment->coach_id;

            if (!isset($bookedSlots[$date])) {
                $bookedSlots[$date] = [];
            }

            if (!isset($bookedSlots[$date][$coachId])) {
                $bookedSlots[$date][$coachId] = [];
            }

            // Extend the time slot to include buffer minutes
            $extendedTimeSlot = self::extendTimeSlotWithBuffer($timeSlot, $bufferMinutes);

            // Store the extended time slot and customer info per coach
            $bookedSlots[$date][$coachId][] = [
                'time' => $extendedTimeSlot,
                'customer' => $appointment->name,
                'coach' => $appointment->coach->name ?? 'Auto-assigned',
                'status' => $appointment->appointment_status,
                'startTime' => $extendedTimeSlot,
                'endTime' => $extendedTimeSlot,
                'totalMinutes' => $appointment->total_minutes ?? 0
            ];
        }



        // Check if admin has Google Calendar connected
        $admin = User::where('user_type', 'Admin')->first();
        $adminGoogleConnected = $admin && $admin->google;

        return view('frontend.app', [
            'bookedSlots' => $bookedSlots,
            'availablity' => $availability, // Keep for backward compatibility
            'coachNames' => $coachNames,
            'coaches' => $coaches,
            'coachAvailability' => $coachAvailability,
            'adminGoogleConnected' => $adminGoogleConnected
        ]);
    }


    /**
     * @param $request
     * @return JsonResponse|RedirectResponse
     */
    public static function bookAppointment($request): JsonResponse|RedirectResponse
    {
        try {
            DB::beginTransaction();

            $bookedRanges = Appointment::where('selected_date', $request->selectedDate)
                ->whereIn('appointment_status', ['Pending', 'Confirmed'])
                ->pluck('selected_time_slot')
                ->toArray();

            $bookedSlots = [];
            $siteSettings = SiteSettings::first();
            foreach ($bookedRanges as $range) {
                $slots = Helper::extractTimeSlots($range, $siteSettings->buffer_minutes); // see helper below
                $bookedSlots = array_merge($bookedSlots, $slots);
            }
            $userSlots = Helper::extractTimeSlots($request->selectedTimeSlot, $siteSettings->buffer_minutes, $siteSettings->buffer_minutes);
            $overlap = array_intersect($bookedSlots, $userSlots);
            if (!empty($overlap)) {
                return response()->json(['errors' => ['One or more slots are already booked.']], 422);
            }

            // Check address eligibility for player type
            $existingAppointment = Appointment::where('address', $request->address)->exists();

            if ($request->playerType === 'FreeTrial' && $existingAppointment) {
                return response()->json(['errors' => ['You are not eligible for free trial player. This address has been used before.']], 422);
            }
            // Check if a specific coach was selected
            if (!empty($request->selectedCoachId)) {
                $coach = User::where('user_type', 'Coach')
                    ->where('coach_type', 'Normal Coach')
                    ->where('id', $request->selectedCoachId)
                    ->where('status', 1)
                    ->whereHas('google')
                    ->first();

                if (!$coach) {
                    return response()->json(['errors' => ['Selected coach is not available.']], 422);
                }

                // Check if this is a free trial player and validate duration limit
                if ($request->playerType === 'FreeTrial') {
                    $totalLessonTime = collect($request->lessons)->sum('duration');
                    if ($totalLessonTime > 60) {
                        return response()->json(['errors' => ['Free trial players cannot book more than 60 minutes total duration. Current total: ' . $totalLessonTime . ' minutes.']], 422);
                    }
                }

                // Validate total lesson time against available slot time
                $totalLessonTime = collect($request->lessons)->sum('duration');

                // Calculate actual available time from the selected time slot
                // Handle time slot format: "1:30 PM - 2:30 PM, Thu, Sep 11, 2025"
                $timeSlotParts = explode(' - ', $request->selectedTimeSlot);
                if (count($timeSlotParts) === 2) {
                    // Extract just the time part from the start time
                    $startTimeStr = trim($timeSlotParts[0]);

                    // Extract just the time part from the end time (remove date part)
                    $endTimeStr = trim(explode(',', $timeSlotParts[1])[0]);

                    try {
                        $startTime = Carbon::createFromFormat('g:i A', $startTimeStr);
                        $endTime = Carbon::createFromFormat('g:i A', $endTimeStr);
                        $availableTime = $endTime->diffInMinutes($startTime);
                    } catch (\Exception $e) {
                        // Fallback to 60 minutes if time parsing fails
                        $availableTime = 60;
                    }
                } else {
                    // Fallback to 60 minutes if time slot parsing fails
                    $availableTime = 60;
                }

                // Apply buffer minutes if specified
                if (!empty($request->selectedBufferMinutes)) {
                    $availableTime = $availableTime - $request->selectedBufferMinutes;
                }

                if ($totalLessonTime > $availableTime) {
                    $bufferInfo = !empty($request->selectedBufferMinutes) ? ' considering buffer minutes (' . $request->selectedBufferMinutes . ' minutes)' : '';
                    return response()->json(['errors' => ['Total lesson time (' . $totalLessonTime . ' minutes) exceeds available slot time (' . $availableTime . ' minutes)' . $bufferInfo . '. Please reduce lesson duration or select a longer time slot.']], 422);
                }

                // Check for time slot conflicts with existing bookings
                $conflictingBookings = Appointment::where('coach_id', $coach->id)
                    ->where('selected_date', $request->selectedDate)
                    ->whereIn('appointment_status', ['Pending', 'Confirmed'])
                    ->get();

                foreach ($conflictingBookings as $existingBooking) {
                    // Handle existing booking time slot format
                    $existingTimeParts = explode(' - ', $existingBooking->selected_time_slot);
                    if (count($existingTimeParts) === 2) {
                        $existingStartTime = Carbon::createFromFormat('g:i A', trim($existingTimeParts[0]));
                        $existingEndTime = Carbon::createFromFormat('g:i A', trim(explode(',', $existingTimeParts[1])[0]));
                    } else {
                        continue; // Skip invalid time slots
                    }

                    // Handle new booking time slot format
                    $newTimeParts = explode(' - ', $request->selectedTimeSlot);
                    if (count($newTimeParts) === 2) {
                        $newStartTime = Carbon::createFromFormat('g:i A', trim($newTimeParts[0]));
                        $newEndTime = $newStartTime->copy()->addMinutes($totalLessonTime);
                    } else {
                        continue; // Skip invalid time slots
                    }

                    // Check if there's any overlap
                    if ($newStartTime < $existingEndTime && $newEndTime > $existingStartTime) {
                        return response()->json(['errors' => ['Selected time slot conflicts with existing booking. Please choose a different time.']], 422);
                    }
                }
            } else {
                // Auto-assign coach if none selected
                $coach = User::where('user_type', 'Coach')->where('coach_type', 'Normal Coach')
                    ->whereHas('google')->where('status', 1)
                    ->when(true, function ($query) {
                        $query->whereDoesntHave('appointments', function ($q) {
                            $q->whereDate('created_at', Carbon::today());
                        });
                    })
                    ->inRandomOrder()->first() ??
                    User::where('user_type', 'Coach')->where('coach_type', 'Normal Coach')->where('status', 1)->whereHas('google')
                        ->inRandomOrder()
                        ->first();

                // Check if this is a free trial player and validate duration limit (for auto-assigned coaches)
                if ($request->playerType === 'FreeTrial') {
                    $totalLessonTime = collect($request->lessons)->sum('duration');
                    if ($totalLessonTime > 60) {
                        return response()->json(['errors' => ['Free trial players cannot book more than 60 minutes total duration. Current total: ' . $totalLessonTime . ' minutes.']], 422);
                    }
                }
            }

            $appointmentId = Appointment::create((new CreateAppointmentDTO($request, $coach))->toArray());

            foreach ($request->lessons as $lesson) {
                Lesson::create((new CreateLessonsDTO($appointmentId->id, $lesson))->toArray());
            }
            DB::commit();

            // Check if it's a free trial player
            if ($request->playerType === 'FreeTrial') {
                // Load the appointment with relationships for free trial processing
                $appointment = Appointment::with(['coach', 'lessons'])->find($appointmentId->id);

                if ($appointment) {
                    // Process free trial booking (send emails and create calendar events)
                    self::processFreeTrialBooking($request, $appointment);
                } else {
                    \Log::error('Failed to load appointment for free trial processing. Appointment ID: ' . $appointmentId->id);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Free trial appointment booked successfully!',
                    'appointment_id' => $appointmentId->id,
                    'is_free_trial' => true
                ]);
            }

            // For regular players, proceed with payment
            return self::performPayment($request, $appointmentId->id);
//            return response()->json(['message' => 'Appointment booked successfuly']);
        } catch (\Exception $e) {
            DB::rollBack();
            return Helper::jsonErrorHandling($request, $e, __FUNCTION__);
        }
    }

    /**
     * @param $request
     * @param $appointmentId
     * @return JsonResponse
     */
    public static function performPayment($request, $appointmentId): JsonResponse
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));
        try {
            DB::beginTransaction();
            $type = 'card';
            $amount = (int)round($request->bookingTotalPrice * 100);
            $currency = 'usd';
            $trxId = Str::uuid()->toString();
            $productName = 'Home Court Advantage';
            $session = Session::create([
                'payment_method_types' => [$type],
                'customer_email' => $request->email, // send email to Stripe
                'line_items' => [[
                    'price_data' => [
                        'currency' => $currency,
                        'product_data' => [
                            'name' => $productName,
                        ],
                        'unit_amount' => $amount,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => 'https://homecourtadvantage-net.beast-hosting.com/success?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => "https://homecourtadvantage-net.beast-hosting.com/payments/api/cancel-payment/",
                'metadata' => [
                    'user_name' => $request->fullName,
                    'trx_id' => $trxId,
                ],
            ]);
            $fullSession = Session::retrieve($session->id);
            $expiresAt = $fullSession->expires_at; // Unix timestamp

            $expiresAtFormatted = date('Y-m-d H:i:s', $expiresAt);
            Transaction::create((new CreateTransactionDTO($trxId, $amount, $type, $currency, $request->bookingSummary, $session->id, $expiresAtFormatted, $appointmentId))->toArray());
            DB::commit();
            return response()->json(['url' => $session->url]);
        } catch (\Exception $e) {
            DB::rollBack();
            return Helper::jsonErrorHandling($request, $e, __FUNCTION__);
        }
    }

    /**
     * @param $request
     * @return JsonResponse
     */
    public static function handleWebhook($request): JsonResponse
    {
        try {
            DB::beginTransaction();
            Stripe::setApiKey(env('STRIPE_SECRET'));

            $payload = $request->getContent();
            $sig_header = $request->server('HTTP_STRIPE_SIGNATURE');
            $endpoint_secret = env('STRIPE_WEBHOOK_SECRET');
            $event = Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
            $customerName = $event->data->object->customer_details;
            $admin = User::where('user_type', 'Admin')->first();
            // Handle event
            switch ($event->type) {
                case 'checkout.session.completed':
                    $session = $event->data->object;
                    $transaction = Transaction::with('appointment')->where('session_id', $session->id)->first();

                    if ($transaction) {
                        // Update parent
                        $transaction->update(['status' => 'Success', 'webhook_data' => json_encode($request->all(), true)]);
                        // Use booking summary from request if available, otherwise fall back to transaction description
                        $bookingDetails = $transaction->description;
                        if (isset($request->bookingSummary) && !empty($request->bookingSummary)) {
                            $bookingDetails = $request->bookingSummary;
                        }

                        // If still no booking details, generate them from the appointment
                        if (empty($bookingDetails) && $transaction->appointment) {
                            $bookingDetails = self::generateBookingDetailsFromAppointment($transaction->appointment);
                        }

                        AppointmentBookingjob::dispatch($customerName->name, $customerName->email, $bookingDetails);
                        AppointmentReceivingjob::dispatch($admin->name, $admin->email, $bookingDetails);
                        // Update related appointment
                        if ($transaction->appointment) {
                            $transaction->appointment->update(['appointment_status' => 'Confirmed']);
                        }
                        $adminCalendarResult = self::assignToGoogleCalender($admin, $transaction);
                        if (is_string($adminCalendarResult) && str_contains($adminCalendarResult, 'not accessible')) {
                            \Log::warning('Admin Google Calendar not accessible: ' . $adminCalendarResult);
                        }

                        if (isset($transaction->appointment->coach)) {
                            $coach = $transaction->appointment->coach;
                            AppointmentReceivingjob::dispatch($coach->name, $coach->email, $bookingDetails);
                            $coachCalendarResult = self::assignToGoogleCalender($transaction->appointment->coach, $transaction);
                            if (is_string($coachCalendarResult) && str_contains($coachCalendarResult, 'not accessible')) {
                                \Log::warning('Coach Google Calendar not accessible: ' . $coachCalendarResult);
                            }
                        }
                    }
                    break;

                case 'payment_intent.payment_failed':
                    $intent = $event->data->object;
                    $transaction = Transaction::with('appointment')->where('session_id', $intent->id)->first();
                    if ($transaction) {
                        // Update parent
                        $transaction->update(['status' => 'Failed', 'webhook_data' => json_encode($request->all(), true)]);
                        // Update related appointment
                        if ($transaction->appointment) {
                            $transaction->appointment->update(['appointment_status' => 'Declined']);
                        }
                    }
                    break;
            }

            DB::commit();
            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            DB::rollBack();
            return Helper::jsonErrorHandling($request, $e, __FUNCTION__);
        }
    }

    public static function assignToGoogleCalender($user, $transaction)
    {
        try {
            $client = Helper::getGoogleClientForUser($user);

        // Check if client is a string (error message)
        if (is_string($client)) {
            \Log::error('Google Calendar connection error: ' . $client);
            return 'Google Calendar is not accessible. Please login to your calendar again.';
        }

            $calendarService = new Google_Service_Calendar($client);

        // Example: "8:00 AM - 8:30 AM, Mon, Aug 4, 2025"
        $slotString = $transaction->appointment->selected_time_slot;

        // Extract times and date
        preg_match('/^(.+?) - (.+?), .*?, (.+)$/', $slotString, $matches);

        if (count($matches) !== 4) {
            session()->flash('errors', 'You need to login to access this feature.');
            return route('dashboard');
        }

        $startTime = $matches[1]; // "8:00 AM"
        $endTime = $matches[2];   // "8:30 AM"
        $date = $matches[3];      // "Aug 4, 2025"

        // Parse to Carbon
        $startDateTime = Carbon::parse("$date $startTime");
        $endDateTime = Carbon::parse("$date $endTime");

        // Create Google Calendar Event
        $event = new Google_Service_Calendar_Event([
            'summary' => 'Tennis Lesson - ' . $transaction->appointment->name,
            'location' => $transaction->appointment->address,
            'description' => $transaction->description ?: self::generateBookingDetailsFromAppointment($transaction->appointment),
            'start' => [
                'dateTime' => $startDateTime->toAtomString(),
                'timeZone' => config('app.timezone'),
            ],
            'end' => [
                'dateTime' => $endDateTime->toAtomString(),
                'timeZone' => config('app.timezone'),
            ],
            'attendees' => [
                ['email' => $transaction->appointment->email],
            ],
            'reminders' => [
                'useDefault' => false,
                'overrides' => [
                    ['method' => 'email', 'minutes' => 24 * 60], // 24 hours before
                    ['method' => 'popup', 'minutes' => 30], // 30 minutes before
                ],
            ],
        ]);
        $createdEvent = $calendarService->events->insert('primary', $event);
        $newEvent = [
            'user_id' => $user->id,
            'event_id' => $createdEvent->id
        ];

        $existingIds = json_decode($transaction->appointment->google_event_id, true) ?? [];

        // Ensure array format
        if (!is_array($existingIds)) {
            $existingIds = [];
        }

        // Preserve first object if exists
        $firstEvent = $existingIds[0] ?? null;

        // Always make the array contain exactly two objects
        if ($firstEvent) {
            $existingIds = [$firstEvent, $newEvent];
        } else {
            $existingIds = [$newEvent]; // first insert, will become position 0
        }

        $transaction->appointment->update([
            'google_event_id' => json_encode($existingIds)
        ]);
        } catch (\Exception $e) {
            \Log::error('Google Calendar event creation failed: ' . $e->getMessage());
            \Log::error('User: ' . $user->id . ', Transaction: ' . $transaction->id);
            \Log::error('Stack trace: ' . $e->getTraceAsString());
        }
    }

    /**
     * Process free trial booking - send emails and create calendar events
     * @param $request
     * @param $appointment
     * @return void
     */
    public static function processFreeTrialBooking($request, $appointment): void
    {
        try {
            \Log::info('Starting free trial booking processing for appointment ID: ' . $appointment->id);

            $admin = User::where('user_type', 'Admin')->first();
            \Log::info('Admin found: ' . ($admin ? $admin->id : 'No admin found'));

            // Prepare booking details for emails
            $bookingDetails = "Free Trial Booking\n";
            $bookingDetails .= "Date: " . $request->selectedDate . "\n";
            $bookingDetails .= "Time: " . $request->selectedTimeSlot . "\n";
            $bookingDetails .= "Player: " . $request->fullName . "\n";
            $bookingDetails .= "Email: " . $request->email . "\n";
            $bookingDetails .= "Phone: " . ($request->phoneNumber ?: 'Phone not available') . "\n";
            $bookingDetails .= "Address: " . $request->address . "\n";
            $bookingDetails .= "Coach: " . ($appointment->coach ? $appointment->coach->name : 'Not assigned') . "\n";

            if (!empty($request->lessons)) {
                $bookingDetails .= "Lessons:\n";
                foreach ($request->lessons as $lesson) {
                    $bookingDetails .= "- " . $lesson['type'] . " (" . $lesson['duration'] . " min)\n";
                    if (!empty($lesson['description'])) {
                        $bookingDetails .= "  Description: " . $lesson['description'] . "\n";
                    }
                }
            }

            \Log::info('Booking details prepared: ' . $bookingDetails);

            // Send emails
            \Log::info('Dispatching player email to: ' . $request->email);
            AppointmentBookingjob::dispatch($request->fullName, $request->email, $bookingDetails);

            if ($admin) {
                \Log::info('Dispatching admin email to: ' . $admin->email);
                AppointmentReceivingjob::dispatch($admin->name, $admin->email, $bookingDetails);
            }

            // Create Google Calendar event for admin if connected
            if ($admin && $admin->google) {
                \Log::info('Creating Google Calendar event for admin: ' . $admin->id);
                $mockTransaction = new \stdClass();
                $mockTransaction->id = 'free-trial-admin-' . $appointment->id;
                $mockTransaction->description = $bookingDetails;
                $mockTransaction->appointment = $appointment;

                $calendarResult = self::assignToGoogleCalender($admin, $mockTransaction);
                if (is_string($calendarResult) && str_contains($calendarResult, 'not connected')) {
                    \Log::warning('Admin Google Calendar not accessible: ' . $calendarResult);
                }
            } else {
                \Log::info('Admin Google Calendar not connected or admin not found');
            }

            // Create Google Calendar event if coach has Google Calendar connected
            if ($appointment->coach && $appointment->coach->google) {
                \Log::info('Creating Google Calendar event for coach: ' . $appointment->coach->id);
                // Create a mock transaction for Google Calendar integration
                $mockTransaction = new \stdClass();
                $mockTransaction->id = 'free-trial-coach-' . $appointment->id;
                $mockTransaction->description = $bookingDetails;
                $mockTransaction->appointment = $appointment;

                $calendarResult = self::assignToGoogleCalender($appointment->coach, $mockTransaction);
                if (is_string($calendarResult) && str_contains($calendarResult, 'not connected')) {
                    \Log::warning('Coach Google Calendar not accessible: ' . $calendarResult);
                }
            } else {
                \Log::info('Coach Google Calendar not connected or coach not found. Coach: ' . ($appointment->coach ? $appointment->coach->id : 'No coach'));
            }

            // Update appointment status to confirmed for free trials
            $appointment->update(['appointment_status' => 'Confirmed']);
            \Log::info('Free trial booking processing completed successfully for appointment ID: ' . $appointment->id);

        } catch (\Exception $e) {
            \Log::error('Free trial booking processing failed: ' . $e->getMessage());
            \Log::error('Appointment ID: ' . $appointment->id);
            \Log::error('Stack trace: ' . $e->getTraceAsString());
        }
    }

    /**
     * Generates booking details from an appointment object.
     * @param $appointment
     * @return string
     */
    private static function generateBookingDetailsFromAppointment($appointment): string
    {
        $bookingDetails = "Appointment Details\n";
        $bookingDetails .= "Date: " . $appointment->selected_date . "\n";
        $bookingDetails .= "Time: " . $appointment->selected_time_slot . "\n";
        $bookingDetails .= "Coach: " . ($appointment->coach ? $appointment->coach->name : 'Not assigned') . "\n";
        $bookingDetails .= "Player: " . ($appointment->name ?: 'Player name not available') . "\n";
        $bookingDetails .= "Email: " . ($appointment->email ?: 'Email not available') . "\n";
        $bookingDetails .= "Phone: " . ($appointment->phone_number ?: 'Phone not available') . "\n";

        if ($appointment->lessons && count($appointment->lessons) > 0) {
            $bookingDetails .= "Lessons:\n";
            foreach ($appointment->lessons as $lesson) {
                $bookingDetails .= "- " . ($lesson->type ?: 'Lesson type not specified') . " (" . ($lesson->duration ?: 'Duration not specified') . " min)\n";
                if (!empty($lesson->description)) {
                    $bookingDetails .= "  Description: " . $lesson->description . "\n";
                }
            }
        }

        return $bookingDetails;
    }

    /**
     * Extend time slot to include buffer minutes
     */
    private static function extendTimeSlotWithBuffer($timeSlot, $bufferMinutes): string
    {
        if ($bufferMinutes <= 0) {
            return $timeSlot;
        }

        // Parse the time slot format: "9:00 AM - 9:30 AM, Thu, Aug 28, 2025"
        $parts = explode(',', $timeSlot, 2);
        if (count($parts) !== 2) {
            return $timeSlot; // Return original if format is unexpected
        }

        $timePart = trim($parts[0]);
        $datePart = trim($parts[1]);

        // Extract start and end times
        $timeRange = explode(' - ', $timePart);
        if (count($timeRange) !== 2) {
            return $timeSlot; // Return original if format is unexpected
        }

        $startTime = trim($timeRange[0]);
        $endTime = trim($timeRange[1]);

        // Add buffer minutes to end time
        $startCarbon = Carbon::createFromFormat('g:i A', $startTime);
        $endCarbon = Carbon::createFromFormat('g:i A', $endTime)->addMinutes($bufferMinutes);

        // Reconstruct the time slot with extended end time
        $extendedTimePart = $startTime . ' - ' . $endCarbon->format('g:i A');
        
        return $extendedTimePart . ', ' . $datePart;
    }
}
