<?php

namespace App\Services\Availability;

use App\Interfaces\Availability\AvailabilityInterface;
use Illuminate\Contracts\Foundation\Application;
use App\DTOs\Availability\SetAvailability;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use App\Models\Availability;

class AvailabilityService implements AvailabilityInterface
{
    /**
     * @return Application|Factory|View|\Illuminate\Foundation\Application
     */
    public static function availabilityView(): Factory|View|\Illuminate\Foundation\Application|Application
    {
        $availability = Availability::where('availability' , 1)->get()->toArray();
        $detail = [];
        foreach ($availability as $key => $value) {
            $detail[$value['day']] = ['start_time' => date('H:i', strtotime($value['start_time'])), 'end_time' => date('H:i', strtotime($value['end_time']))];

        }
        return view('backend.availability.availability' , ['detail' => $detail]);
    }

    /**
     * @param $request
     * @return RedirectResponse
     */
    public static function createAvailability($request): RedirectResponse
    {
        $days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

        foreach ($days as $day) {
            if ($request->boolean($day)) {
                $startTime = $request->input("{$day}_start_time");
                $endTime   = $request->input("{$day}_end_time");
                if ($startTime && $endTime && $endTime > $startTime) {
                    // Update or create availability for selected day
                    Availability::updateOrCreate(
                        ['day' => ucfirst($day)],
                        (new SetAvailability($day, $startTime, $endTime))->toArray()
                    );
                }
            } else {
                // Mark availability off for unselected days
                Availability::updateOrCreate(
                    ['day' => ucfirst($day)],
                    ['availability' => 0]
                );
            }
        }
        session()->flash('success', 'Availability set successfully');
        return redirect()->route('availabilityView');
    }
}
