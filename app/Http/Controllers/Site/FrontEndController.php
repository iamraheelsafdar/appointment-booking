<?php

namespace App\Http\Controllers\Site;

use App\Http\Requests\Appointment\AppointmentRequest;
use Illuminate\Foundation\Application;
use App\Services\Site\FrontEndService;
use Illuminate\Contracts\View\Factory;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FrontEndController extends Controller
{

    /**
     * @return View|Factory|Application|\Illuminate\Contracts\Foundation\Application
     */
    public function frontendView(): View|Factory|Application|\Illuminate\Contracts\Foundation\Application
    {
        return FrontEndService::frontendView();
    }

    /**
     * @param AppointmentRequest $request
     * @return JsonResponse|RedirectResponse
     */
    public function bookAppointment(AppointmentRequest $request): JsonResponse|RedirectResponse
    {
        return FrontEndService::bookAppointment($request);
    }

    /**
     * @param AppointmentRequest $request
     * @return JsonResponse|RedirectResponse
     */
    public function handleWebhook(Request $request): JsonResponse|RedirectResponse
    {
        return FrontEndService::handleWebhook($request);
    }

    public function test(Request $request): JsonResponse
    {
        $idToken = $request['id_token'];
        $parts = explode(".", $idToken);
        $payload = json_decode(base64_decode($parts[1]), true);
        dd($payload);
    }
}
