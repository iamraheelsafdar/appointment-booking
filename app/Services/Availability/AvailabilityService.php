<?php

namespace App\Services\Availability;

use App\Interfaces\Availability\AvailabilityInterface;
use Illuminate\Contracts\Foundation\Application;
use App\DTOs\Availability\SetAvailability;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use App\Models\Availability;

class AvailabilityService implements AvailabilityInterface
{
    /**
     * @param $id
     * @return Application|Factory|View|\Illuminate\Foundation\Application|RedirectResponse
     */
    public static function availabilityView($id): Application|Factory|View|\Illuminate\Foundation\Application|RedirectResponse
    {
        $availabilities = Availability::where('user_id', $id)
            ->where('is_active', true)
            ->orderBy('day')
            ->orderBy('start_time')
            ->get()
            ->groupBy('day');
        return view('backend.availability.availability', ['availabilities' => $availabilities]);
    }

    /**
     * @param $request
     * @return JsonResponse
     */
    public static function createAvailability($request): JsonResponse
    {
        Availability::where('user_id', $request->user_id)->delete();

        // Insert new availabilities
        foreach ($request->availabilities as $availability) {
            Availability::create([
                'user_id' => $request->user_id,
                'day' => $availability['day'],
                'start_time' => $availability['start_time'],
                'end_time' => $availability['end_time'],
                'is_active' => true
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Availability updated successfully!'
        ]);
    }

    public static function deleteAvailability($request): JsonResponse
    {
        $availability = Availability::where('id', $request->id)
            ->where('user_id', $request->user_id)
            ->first();

        if (!$availability) {
            return response()->json([
                'success' => false,
                'message' => 'Availability slot not found.'
            ], 404);
        }

        $availability->delete();

        return response()->json([
            'success' => true,
            'message' => 'Availability slot removed successfully!'
        ]);
    }
}
