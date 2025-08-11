<?php

namespace App\Services\Appointment;

use App\Http\Resources\Appointment\GetAppointmentResource;
use App\Interfaces\Appointment\AppointmentInterface;
use App\Filters\Appointment\AppointmentDateFilter;
use App\Jobs\AppointmentBookingjob;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Site\FrontEndService;
use Illuminate\Contracts\Foundation\Application;
use App\DTOs\Appointment\CreateAppointmentDTO;
use App\DTOs\Lessons\CreateLessonsDTO;
use Illuminate\Contracts\View\Factory;
use App\Http\Resources\DataCollection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Http\Response;
use App\Models\Appointment;
use App\Models\Lesson;
use App\Helper;

class AppointmentService implements AppointmentInterface
{

    /**
     * @param $request
     * @return Application|Factory|View|\Illuminate\Foundation\Application
     */
    public static function appointmentsView($request): Factory|View|\Illuminate\Foundation\Application|Application
    {
        $appointments = app(Pipeline::class)
            ->send(Appointment::query())
            ->through([
                AppointmentDateFilter::class,
            ])
            ->thenReturn()
            ->latest()
            ->orderBy('id', 'desc')
            ->paginate($request->per_page ?? 10);


        $appointmentsCollection = new DataCollection($appointments);
        $appointmentsCollection->setResourceClass(GetAppointmentResource::class);
        $appointments = $appointmentsCollection->toArray($request);

        return view('backend.appointment.get-appointment', ['appointments' => $appointments]);
    }

    /**
     * @param $id
     * @return View|\Illuminate\Foundation\Application|Factory|Application|RedirectResponse
     */
    public static function updateAppointmentsView($id): View|\Illuminate\Foundation\Application|Factory|Application|RedirectResponse
    {
        $appointment = Appointment::find($id);
        if (!$appointment) {
            session()->flash('errors', "User not found.");
            return redirect()->back();
        }
        $assignUser = User::whereHas('Google')->where('user_type', 'Coach')->where('status', '1')->pluck('id', 'name')->toArray();
        return view('backend.appointment.update-appointment', ['appointment' => $appointment, 'users' => $assignUser]);
    }

    /**
     * @param $request
     * @return Response|RedirectResponse
     */
    public static function updateAppointments($request): Response|RedirectResponse
    {
        try {
            DB::beginTransaction();
            $appointment = Appointment::find($request->id);
            if ($request->appointment_status == 'Declined' || $request->appointment_status == 'Rejected') {

                if ($appointment->google_event_id) {
                    foreach (json_decode($appointment->google_event_id, true) as $detail) {
                        $user = User::where('id', $detail['user_id'])->first();
                        Helper::removeBooking($user, $detail['event_id'], $request);
                    }
                }
            }
            $assignAppointment = Appointment::where('id', $request->id)->where('coach_id', '!=', $request['assign_to'])->first();
            if ($assignAppointment) {
                $transaction = Transaction::where('appointment_id', $assignAppointment->id)->first();
                if ($transaction) {
                    $assignAppointment->update(['coach_id' => $request['assign_to']]);
                    $removeAppointment = json_decode($appointment->google_event_id, true);
                    if (isset($removeAppointment[1])) {
                        $user = User::where('id', $removeAppointment[1]['user_id'])->first();
                        Helper::removeBooking($user, $removeAppointment[1]['event_id'], $request);
                    }
                    $user = User::where('id', $request['assign_to'])->first();
                    AppointmentBookingjob::dispatch($user->name,$user->email,$appointment->description);
                    FrontEndService::assignToGoogleCalender($user, $transaction);
                }

            }


            $appointment->update([
                'appointment_status' => $request->appointment_status
            ]);
            session()->flash('success', "Appointment status updated successfully.");
            DB::commit();
            return redirect()->route('appointmentsView');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('errors', "Something went wrong");
            return Helper::errorHandling($request, $e, __FUNCTION__);
        }
    }
}
