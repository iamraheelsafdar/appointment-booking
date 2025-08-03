<?php

namespace App\Services\Site;

use App\Interfaces\Site\FrontEndInterface;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\Contracts\View\View;

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

        return view('frontend.app', ['bookedSlots' => $bookedSlots]);
    }

}
