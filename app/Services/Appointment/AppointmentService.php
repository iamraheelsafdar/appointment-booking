<?php

namespace App\Services\Appointment;

use App\Http\Resources\Appointment\GetAppointmentResource;
use App\Interfaces\Appointment\AppointmentInterface;
use App\Filters\Appointment\AppointmentDateFilter;
use App\Jobs\AppointmentBookingjob;
use App\Jobs\AppointmentCancellationJob;
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

            // Check if admin has already cancelled this appointment
            if ($appointment->appointment_status === 'Cancelled' && auth()->user()->user_type === 'Admin') {
                session()->flash('error', "Cannot change status of a cancelled appointment.");
                return redirect()->back();
            }

            if ($request->appointment_status == 'Declined' || $request->appointment_status == 'Rejected') {
                // Update appointment status first so emails show correct status
                $appointment->update(['appointment_status' => $request->appointment_status]);

                if ($appointment->google_event_id) {
                    foreach (json_decode($appointment->google_event_id, true) as $detail) {
                        $user = User::where('id', $detail['user_id'])->first();
                        Helper::removeBooking($user, $detail['event_id'], $request);
                    }
                }

                // Send cancellation emails
                self::sendCancellationEmails($appointment, $request->appointment_status);
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
                    AppointmentReceivingjob::dispatch($user->name, $user->email, $appointment->description);
                    FrontEndService::assignToGoogleCalender($user, $transaction);
                }

            }


            // Status already updated above for declined/rejected appointments
            if ($request->appointment_status != 'Declined' && $request->appointment_status != 'Rejected') {
                $appointment->update([
                    'appointment_status' => $request->appointment_status
                ]);
            }
            session()->flash('success', "Appointment status updated successfully.");
            DB::commit();
            return redirect()->route('appointmentsView');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('errors', "Something went wrong");
            return Helper::errorHandling($request, $e, __FUNCTION__);
        }
    }

    /**
     * Send cancellation emails to customer, coach, and admin
     */
    private static function sendCancellationEmails($appointment, $status)
    {
        try {
            // Generate booking details for email
            $bookingDetails = self::generateBookingDetailsForCancellation($appointment);

            // Get customer information
            $customerName = $appointment->full_name ?? 'Customer';
            $customerEmail = $appointment->email ?? null;

            // Get coach information
            $coachName = null;
            $coachEmail = null;
            if ($appointment->coach) {
                $coachName = $appointment->coach->name;
                $coachEmail = $appointment->coach->email;
            }

            // Get admin information
            $admin = User::where('user_type', 'Admin')->first();
            $adminName = $admin ? $admin->name : null;
            $adminEmail = $admin ? $admin->email : null;

            // Determine cancellation reason
            $cancellationReason = $status === 'Declined' ? 'Appointment has been declined' : 'Appointment has been rejected';

            // Dispatch cancellation job
            $cancellationJob = new AppointmentCancellationJob(
                $customerName,
                $customerEmail,
                $bookingDetails,
                $cancellationReason
            );

            if ($coachName && $coachEmail) {
                $cancellationJob->setCoachInfo($coachName, $coachEmail);
            }

            if ($adminName && $adminEmail) {
                $cancellationJob->setAdminInfo($adminName, $adminEmail);
            }

            dispatch($cancellationJob);

            \Log::info("Cancellation emails dispatched for appointment ID: {$appointment->id}");

        } catch (\Exception $e) {
            \Log::error("Failed to send cancellation emails for appointment ID: {$appointment->id}. Error: " . $e->getMessage());
        }
    }

    /**
     * Generate booking details for cancellation email
     */
    private static function generateBookingDetailsForCancellation($appointment)
    {
        $details = "Appointment Details:\n";
        $details .= "Date: " . ($appointment->selected_date ?? 'N/A') . "\n";
        $details .= "Time: " . ($appointment->selected_time_slot ?? 'N/A') . "\n";
        $details .= "Player: " . ($appointment->name ?? 'N/A') . "\n";
        $details .= "Email: " . ($appointment->email ?? 'N/A') . "\n";
        $details .= "Phone: " . ($appointment->phone_number ?? 'N/A') . "\n";
        $details .= "Address: " . ($appointment->address ?? 'N/A') . "\n";
        $details .= "Coach: " . ($appointment->coach ? $appointment->coach->name : 'Not assigned') . "\n";
        $details .= "Status: " . ($appointment->appointment_status ?? 'N/A') . "\n";
        
        // Add lessons if available
        if ($appointment->lessons && $appointment->lessons->count() > 0) {
            $details .= "\nLessons:\n";
            foreach ($appointment->lessons as $lesson) {
                $details .= "- " . ($lesson->type ?? 'N/A') . " (" . ($lesson->duration ?? 0) . " min)\n";
                if ($lesson->description) {
                    $details .= "  Description: " . $lesson->description . "\n";
                }
            }
        }
        
        return $details;
    }
}
