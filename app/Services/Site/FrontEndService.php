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
        $appointments = Appointment::whereIn('appointment_status', ['Pending', 'Confirmed'])->get();
        $bookedSlots = [];

        foreach ($appointments as $appointment) {
            $timePart = explode(',', $appointment->selected_time_slot)[0]; // e.g. "12:00 PM - 3:15 PM"
            [$startTime, $endTime] = explode(' - ', $timePart);

            // Convert to 24-hour format
            $start = Carbon::createFromFormat('g:i A', trim($startTime))->format('H:i');
            $end = Carbon::createFromFormat('g:i A', trim($endTime))->format('H:i');

            // Generate 15-minute interval slots
            $current = Carbon::createFromFormat('H:i', $start);
            $endObj = Carbon::createFromFormat('H:i', $end);
            $slots = [];

            while ($current <= $endObj) {
                $slots[] = $current->format('H:i');
                $current->addMinutes(15);
            }

            // Group by selected_date
            $date = $appointment->selected_date;
            if (!isset($bookedSlots[$date])) {
                $bookedSlots[$date] = [];
            }

            $bookedSlots[$date] = array_values(array_unique(array_merge($bookedSlots[$date], $slots)));
        }

        $availablities = Availability::where('availability', 1)->get()->toArray();
        $dayIndexes = [
            'Sunday' => 0,
            'Monday' => 1,
            'Tuesday' => 2,
            'Wednesday' => 3,
            'Thursday' => 4,
            'Friday' => 5,
            'Saturday' => 6,
        ];

        $availablity = [];

        foreach ($availablities as $value) {
            $day = $value['day'];
            if (isset($dayIndexes[$day])) {
                $index = $dayIndexes[$day];
                $availablity[$index] = [
                    'startTime' => Carbon::createFromFormat('H:i:s', $value['start_time'])->format('H:i'),
                    'endTime' => Carbon::createFromFormat('H:i:s', $value['end_time'])->format('H:i'),
                ];
            }
        }

        return view('frontend.app', ['bookedSlots' => $bookedSlots, 'availablity' => $availablity]);
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

            $appointmentId = Appointment::create((new CreateAppointmentDTO($request, $coach))->toArray());

            foreach ($request->lessons as $lesson) {
                Lesson::create((new CreateLessonsDTO($appointmentId->id, $lesson))->toArray());
            }
            DB::commit();
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
                        AppointmentBookingjob::dispatch($customerName->name, $customerName->email, $transaction->description);
                        AppointmentReceivingjob::dispatch($admin->name, $admin->email, $transaction->description);
                        // Update related appointment
                        if ($transaction->appointment) {
                            $transaction->appointment->update(['appointment_status' => 'Confirmed']);
                        }
                        self::assignToGoogleCalender($admin, $transaction);

                        if (isset($transaction->appointment->coach)) {
                            $coach = $transaction->appointment->coach;
                            AppointmentReceivingjob::dispatch($coach->name, $coach->email, $transaction->description);
                            self::assignToGoogleCalender($transaction->appointment->coach, $transaction);
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
        $client = Helper::getGoogleClientForUser($user);
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
            'summary' => 'New Booking - Home Court Advantage',
            'location' => $transaction->appointment->address,
            'description' => $transaction->description,
            'start' => [
                'dateTime' => $startDateTime->toAtomString(),
                'timeZone' => config('app.timezone'),
            ],
            'end' => [
                'dateTime' => $endDateTime->toAtomString(),
                'timeZone' => config('app.timezone'),
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
    }
}
