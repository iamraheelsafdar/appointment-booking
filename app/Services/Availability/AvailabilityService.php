<?php

namespace App\Services\Availability;

use App\DTOs\Availability\SetAvailability;
use App\Interfaces\Availability\AvailabilityInterface;
use Illuminate\Contracts\Foundation\Application;
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
        $availability = Availability::all()->toArray();
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
            if ($request->has($day)) {
                $startTime = $request->input("{$day}_start_time");
                $endTime = $request->input("{$day}_end_time");

                if ($startTime && $endTime && $endTime > $startTime) {
                    Availability::updateOrcreate(['day' => ucfirst($day)],
                        (new SetAvailability($day, $startTime, $endTime))->toArray());
                }
            }
        }
        session()->flash('success', 'Availability set successfully');
        return redirect()->route('availabilityView');
    }
}
