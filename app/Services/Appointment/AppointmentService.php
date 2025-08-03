<?php

namespace App\Services\Appointment;

use App\Http\Resources\Appointment\GetAppointmentResource;
use App\Interfaces\Appointment\AppointmentInterface;
use App\Filters\Appointment\AppointmentDateFilter;
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
     * @return JsonResponse
     */
    public static function bookAppointment($request): JsonResponse
    {
        try {
            DB::beginTransaction();
            $appointmentId = Appointment::create((new CreateAppointmentDTO($request))->toArray());
            foreach ($request->lessons as $lesson) {
                Lesson::create((new CreateLessonsDTO($appointmentId->id, $lesson))->toArray());
            }
            DB::commit();
            return response()->json(['message' => 'Appointment booked successfuly']);
        } catch (\Exception $e) {
            DB::rollBack();
            return Helper::jsonErrorHandling($request, $e, __FUNCTION__);
        }
    }

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
//            ->where('id', auth()->user()->id)
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
        return view('backend.appointment.update-appointment', ['appointment' => $appointment]);
    }

    /**
     * @param $request
     * @return Response|RedirectResponse
     */
    public static function updateAppointments($request): Response|RedirectResponse
    {
        try {
            DB::beginTransaction();
            $user = Appointment::find($request->id);
            $user->update([
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
