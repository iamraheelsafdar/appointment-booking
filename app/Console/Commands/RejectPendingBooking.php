<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\User;
use App\Jobs\AppointmentCancellationJob;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RejectPendingBooking extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reject-pending-booking';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command is use to reject all the pending appointment after 10 min';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting auto-rejection of pending appointments...');

        $cutoffTime = Carbon::now()->subMinutes(5);
        $pendingAppointments = Appointment::where('appointment_status', 'Pending')
            ->where('created_at', '<', $cutoffTime)
            ->get();

        $rejectedCount = 0;

        foreach ($pendingAppointments as $appointment) {
            $this->info("Rejecting appointment ID: {$appointment->id} (Created: {$appointment->created_at})");
            
            // Update appointment status first so emails show correct status
            $appointment->update(['appointment_status' => 'Declined']);
            
            // Send cancellation emails
            $this->sendCancellationEmails($appointment);
            
            $rejectedCount++;
        }

        $this->info("Auto-rejection completed.");
        $this->info("Rejected: {$rejectedCount} appointments");
    }

    /**
     * Send cancellation emails to customer, coach, and admin
     */
    private function sendCancellationEmails($appointment)
    {
        try {
            // Generate booking details for email
            $bookingDetails = $this->generateBookingDetailsForCancellation($appointment);
            
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
            
            // Dispatch cancellation job
            $cancellationJob = new AppointmentCancellationJob(
                $customerName, 
                $customerEmail, 
                $bookingDetails, 
                'Appointment automatically declined due to timeout (5 minutes)'
            );
            
            if ($coachName && $coachEmail) {
                $cancellationJob->setCoachInfo($coachName, $coachEmail);
            }
            
            if ($adminName && $adminEmail) {
                $cancellationJob->setAdminInfo($adminName, $adminEmail);
            }
            
            dispatch($cancellationJob);
            
            $this->info("  Cancellation emails dispatched for appointment ID: {$appointment->id}");
            
        } catch (\Exception $e) {
            $this->error("  Failed to send cancellation emails for appointment ID: {$appointment->id}. Error: " . $e->getMessage());
        }
    }

    /**
     * Generate booking details for cancellation email
     */
    private function generateBookingDetailsForCancellation($appointment)
    {
        $details = "Appointment Details:\n";
        $details .= "Date: " . ($appointment->selected_date ?? 'N/A') . "\n";
        $details .= "Time: " . ($appointment->selected_time_slot ?? 'N/A') . "\n";
        $details .= "Player: " . ($appointment->name ?? 'N/A') . "\n";
        $details .= "Email: " . ($appointment->email ?? 'N/A') . "\n";
        $details .= "Phone: " . ($appointment->phone_number ?? 'N/A') . "\n";
        $details .= "Address: " . ($appointment->address ?? 'N/A') . "\n";
        $details .= "Coach: " . ($appointment->coach ? $appointment->coach->name : 'Not assigned') . "\n";
        $details .= "Status: " . ($appointment->appointment_status ?? 'Declined') . " (Auto-rejected after 5 minutes)\n";
        
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
