<?php

namespace App\Http\Controllers\Availability;

use App\Http\Requests\Availability\CreateAvailabilityRequest;
use App\Services\Availability\AvailabilityService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    /**
     * @param $id
     * @return View|\Illuminate\Foundation\Application|Factory|Application|RedirectResponse
     */
    public function availabilityView($id): View|\Illuminate\Foundation\Application|Factory|Application|RedirectResponse
    {
        return AvailabilityService::availabilityView($id);
    }

    /**
     * @param CreateAvailabilityRequest $request
     * @return JsonResponse
     */
    public function createAvailability(CreateAvailabilityRequest $request): JsonResponse
    {
        return AvailabilityService::createAvailability($request);
    }

    /**
     * @param CreateAvailabilityRequest $request
     * @return JsonResponse
     */
    public function deleteAvailability(Request $request): JsonResponse
    {
        return AvailabilityService::deleteAvailability($request);
    }
}
