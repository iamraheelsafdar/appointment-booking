<?php

namespace App\Http\Controllers\Availability;

use App\Http\Controllers\Controller;
use App\Http\Requests\Availability\AvailabilityRequest;
use App\Services\Availability\AvailabilityService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    /**
     * @return View|\Illuminate\Foundation\Application|Factory|Application
     */
    public function availabilityView(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return AvailabilityService::availabilityView();
    }

    /**
     * @param AvailabilityRequest $request
     * @return RedirectResponse
     */
    public function createAvailability(Request $request): RedirectResponse
    {
        return AvailabilityService::createAvailability($request);
    }
}
