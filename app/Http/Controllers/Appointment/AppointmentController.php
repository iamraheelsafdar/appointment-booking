<?php

namespace App\Http\Controllers\Appointment;

use App\Services\Appointment\AppointmentService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{

    /**
     * @param Request $request
     * @return Factory|View|Application|\Illuminate\Contracts\Foundation\Application
     */
    public function appointmentsView(Request $request): Factory|View|Application|\Illuminate\Contracts\Foundation\Application
    {
        return AppointmentService::appointmentsView($request);
    }

    /**
     * @param $id
     * @return Factory|View|Application|\Illuminate\Contracts\Foundation\Application
     */
    public function updateAppointmentsView($id): Factory|View|Application|\Illuminate\Contracts\Foundation\Application
    {
        return AppointmentService::updateAppointmentsView($id);
    }

    /**
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function updateAppointments(Request $request): RedirectResponse|Response
    {
        return AppointmentService::updateAppointments($request);
    }
}
